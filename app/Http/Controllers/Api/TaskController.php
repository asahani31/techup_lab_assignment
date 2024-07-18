<?php

namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use App\Models\tasks;
use App\Models\notes;
use Illuminate\Support\Facades\Auth;
use Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;

class TaskController extends BaseController
{
    /**
     * Get all tasks with notes api
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$filter=[])
    {
        $allRecords = tasks::with('notes');
        // order by priority high
        $allRecords = $allRecords->orderByRaw("FIELD(tk_priority, 'high', 'medium', 'low')");
        // order by max count of notes
        $allRecords = $allRecords->withCount('notes')->orderBy('notes_count', 'desc');
        // filter 
        if ($request->has('filter')) {
            // 1-status
            if(isset($request->filter['status'])){
                $status = $request->filter['status'];
                $allRecords = $allRecords->where('tk_status', 'like', "%$status%");
            }
            // 2-due date
            if (isset($request->filter['due_date'])){
                $due_date = $request->filter['due_date'];
                $allRecords = $allRecords->whereDate('tk_due_date', $due_date);
            }
            // 3-priority
            if (isset($request->filter['priority'])){
                $priority = $request->filter['priority'];
                $allRecords = $allRecords->where('tk_priority', 'like', "%$priority%");
            }
            // 4-min one note
            if (isset($request->filter['note'])){
                $note = $request->filter['note'];
                $allRecords = $allRecords->whereHas('notes', function ($query) {
                    return $query->where('nt_attachment', '!=', null);
                });
            }
        }

        $response = $allRecords->get();

        if (!empty($response)) {
            $success['Tasks'] = $response;
            if( $response->count()==0){
                return $this->sendResponse($success, 'No tasks with notes available'); 
            }
            return $this->sendResponse($success, 'All tasks with notes retrieved successfully');
        } else {
            return $this->sendError('error', 'Record not found.', 422);
        }
       
    }

     /**
     * Create task with multiple notes api
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tk_subject' => 'required',
            'tk_description' => 'required',
            'tk_start_date' => 'required|date|after_or_equal:now|date_format:Y-m-d',
            'tk_due_date' => 'required|date|after_or_equal:tk_start_date|date_format:Y-m-d',
            'tk_status' => 'required|string|in:new,incomplete,complete',
            'tk_priority' => 'required|string|in:,high,medium,low',
            'notes' => 'required|array',
            'notes.*.nt_subject' => 'required',
            'notes.*.nt_attachment' => 'required',
            'notes.*.nt_note' => 'required',
        ]);
        
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input = $request->all();
        $taskinput = [];
        $taskinput['tk_subject'] = $input['tk_subject'];
        $taskinput['tk_description'] = $input['tk_description'];
        $taskinput['tk_start_date'] = $input['tk_start_date'];
        $taskinput['tk_due_date'] = $input['tk_due_date'];
        $taskinput['tk_status'] = $input['tk_status'];
        $taskinput['tk_priority'] = $input['tk_priority'];
        $taskinput["created_at"] = Carbon::now();
        $taskinput["updated_at"] = Carbon::now();
        $task_created = tasks::insertGetId($taskinput);

        $notesinputs = $input['notes']; 
        $notesinput = []; 
        foreach($notesinputs as $k=>$newdata){
            $notesinput[$k]['nt_subject'] = $newdata['nt_subject'];
            $notesinput[$k]['nt_note'] = $newdata['nt_note'];
            foreach($newdata['nt_attachment'] as $j=>$file)
            { 
                //save full adress of image
                $patch = $file->store('images');
                $patch_url = url('storage/') . '/' . $patch;
                //store image file into directory and db
                $nt_attachment_arr[] = [
                    'nt_attachment' => $patch,
                ];
            }
            $notesinput[$k]['nt_attachment'] = json_encode($nt_attachment_arr);
            $notesinput[$k]['task_id'] = $task_created;
            $taskinput[$k]["created_at"] = Carbon::now();
            $taskinput[$k]["updated_at"] = Carbon::now();
        }
        $notes_created = notes::insert($notesinput);
        if (!$notes_created) {
            return $this->sendError('error', 'Server Error', 400);
        } else {
            $success['taskinput'] = $taskinput;
            $success['notesinput'] = $notesinput;
            return $this->sendResponse($success, 'Task with multiple notes created successfully.');
        }
       
    }


    
}