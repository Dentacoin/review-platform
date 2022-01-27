@if($item->type=='mobident')
	
@else
	<a href="{{ url('/cms/users/users/edit/'.$item->user_id) }}">
		{{ !empty($item->user) ? $item->user->name : ''  }}
	</a>

	@if($item->status == 'first')
		<div class="user-info-wrapper">
			<div class="img-wrap user-info" user-id="{{ $item->user_id }}">
		        <img src="{{ url('img/info-green.png') }}" style="max-width: 15px;">
		    </div>

		    <div class="user-info-tooltip">
		    </div>
		</div>
	@else
		<br/>
	@endif
@endif

<br/>
{{ !empty($item->user) ? $item->user->email : ''  }}
<br/>
<br/>
@if(!empty($item->user))
	@if($item->user->is_dentist)
		<span class="label label-{{ config('user-statuses-classes')[$item->user->status] }}">{{ config('user-statuses')[$item->user->status] }}</span>
	@else
		@if(!empty($item->user->patient_status))
			<span class="label label-{{ config('user-statuses-classes')[$item->user->patient_status] }}">{{ config('patient-statuses')[$item->user->patient_status] }}</span>
		@endif
	@endif
@else
	@if($item->is_dentist)
		<span class="label label-{{ config('user-statuses-classes')[$item->status] }}">{{ config('user-statuses')[$item->status] }}</span>
	@else
		@if(!empty($item->patient_status))
			<span class="label label-{{ config('user-statuses-classes')[$item->patient_status] }}">{{ config('patient-statuses')[$item->patient_status] }}</span>
		@endif
	@endif
@endif
<br/>