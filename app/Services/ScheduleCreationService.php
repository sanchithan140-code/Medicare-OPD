<?php
namespace App\Services;
use App\Models\{Doctor,DoctorSchedule,DoctorLeave};
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ScheduleCreationService
{
    public function create(array $data,int $actor): DoctorSchedule
    {
        return DB::transaction(function()use($data,$actor){
            // Serialize room allocation across doctors, including an empty schedule date.
            Doctor::orderBy('doctor_id')->lockForUpdate()->get(['doctor_id']);
            $doctor=Doctor::with('user')->findOrFail($data['doctor_id']);
            if(!$doctor->active || $doctor->user->account_status!=='ACTIVE')throw ValidationException::withMessages(['doctor_id'=>'This doctor is not available for scheduling.']);
            $start=Carbon::parse($data['schedule_date'].' '.$data['start_time']);
            $end=Carbon::parse($data['schedule_date'].' '.$data['end_time']);
            if(!$start->isFuture())throw ValidationException::withMessages(['start_time'=>'Choose a future session start time.']);
            $capacity=intdiv((int)$start->diffInMinutes($end),(int)$data['slot_minutes']);
            if($data['capacity']>$capacity)throw ValidationException::withMessages(['capacity'=>"This session can contain at most {$capacity} slots."]);
            $room=$this->room($data['room_no'] ?? '');
            $overlaps=DoctorSchedule::whereDate('schedule_date',$data['schedule_date'])->whereNotIn('status',['CANCELLED','CLOSED'])
                ->where('start_time','<',$data['end_time'])->where('end_time','>',$data['start_time'])->lockForUpdate()->get();
            foreach($overlaps as $existing){
                if($existing->doctor_id===$doctor->doctor_id)throw ValidationException::withMessages(['start_time'=>'This doctor already has an overlapping session.']);
                if($room!=='' && $room===$this->room($existing->room_no ?? ''))throw ValidationException::withMessages(['room_no'=>'This room is already assigned during the selected time.']);
            }
            if(DoctorLeave::where('doctor_id',$doctor->doctor_id)->where('status','APPROVED')->where('start_at','<',$end)->where('end_at','>',$start)->exists())throw ValidationException::withMessages(['schedule_date'=>'This doctor has approved leave during the selected time.']);
            $schedule=DoctorSchedule::create($data+['status'=>'OPEN','created_by'=>$actor]);
            for($i=1;$i<=$data['capacity'];$i++){
                $slotEnd=$start->copy()->addMinutes((int)$data['slot_minutes']);
                $schedule->slots()->create(['slot_start'=>$start->copy(),'slot_end'=>$slotEnd,'slot_number'=>$i,'slot_status'=>'AVAILABLE']);
                $start=$slotEnd;
            }
            return $schedule;
        });
    }
    private function room(string $value):string
    {
        $value=strtoupper(trim($value));
        if(preg_match('/^(?:OPD[ -]*)?0*([1-7])$/',$value,$match))return 'OPD-'.$match[1];
        return $value;
    }
}
