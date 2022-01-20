@extends('admin')

@section('content')

    <h1 class="page-header">Users Rewards</h1>
    <!-- end page-header -->

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-default" data-click="panel-expand"><i class="fa fa-expand"></i></a>
                    </div>
                    <h4 class="panel-title">Rewards filter</h4>
                </div>
                <div class="panel-body users-filters">
                    <form method="get" action="{{ url('cms/users/rewards/') }}" id="users-rewards-form">
                        <div class="row custom-row" style="margin-bottom: 10px;">
                            <div class="col-md-1">
                                <input type="text" class="form-control" name="search-user-id" value="{{ $search_user_id }}" placeholder="User ID">
                            </div>
                            <div class="col-md-2">
                                <input type="text" class="form-control" name="search-email" value="{{ $search_email }}" placeholder="User Email">
                            </div>
                            <div class="col-md-2">
                                <select class="form-control" name="search-type">
                                    <option value="">Reward Type</option>
                                    @foreach(config('rewards-type') as $k => $v)
                                        <option value="{{ $k }}" {!! $k==$search_type ? 'selected="selected"' : '' !!}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>                            
                            <div class="col-md-2">
                                <input type="submit" class="btn btn-sm btn-primary btn-block" name="search" value="Search">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">
                    <h4 class="panel-title">Users Rewards</h4>
                </div>
                <div class="panel-body" id="link">
                    <div class="row table-responsive-md">
                        <p>Total count: {{ $total_count }}</p>
                        <p>Sum: {{ $sum_price }} DCN</p>
                        <table class="table table-striped table-question-list">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Platform</th>
                                    <th>Reward for</th>
                                    <th>DCN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $reward)
                                    <tr>
                                        <td>
                                            {{ $reward->id }}
                                        </td>
                                        <td>
                                            {{ $reward->created_at ? date('d.m.Y, H:i:s', $reward->created_at->timestamp) : '' }}
                                        </td>
                                        <td>
                                            <a href="{{ url('cms/users/users/edit/'.$reward->user_id) }}"> {{ !empty($reward->user) ? $reward->user->name : 'unknown' }}</a>
                                        </td>
                                        <td>
                                            @if($reward->type)
                                                {{ config('rewards-type')[$reward->type] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($reward->platform)
                                                {{ config('platforms')[$reward->platform]['name'] }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($reward->reference_id)
                                                @if($reward->type == 'survey')

                                                    @php($survey = App\Models\Vox::withTrashed()->find($reward->reference_id))
                                                    @if($survey)
                                                        <a href="{{ url('cms/vox/edit/'.$survey->id) }}"> {{ $survey->title }} </a>
                                                    @endif

                                                @elseif($reward->type == 'review' || $reward->type == 'dentist-review' || $reward->type == 'review_trusted')

                                                    @php($review = App\Models\Review::withTrashed()->find($reward->reference_id))
                                                    @if($review)
                                                        <a href="{{ url('cms/trp/reviews/?id='.$review->id) }}">Review</a>
                                                    @endif

                                                @elseif($reward->type == 'invitation')

                                                    @php($invitation = App\Models\UserInvite::find($reward->reference_id))
                                                    @if(!empty($invitation))
                                                        <a href="{{ url('cms/invites/?id='.$invitation->id) }}">Invitation</a>
                                                    @endif

                                                @elseif($reward->type == 'added_dentist')

                                                    @php($user = App\Models\User::withTrashed()->find($reward->reference_id))
                                                    @if($user)
                                                        <a href="{{ url('cms/users/users/edit/'.$user->id) }}">{{ $user->getNames() }}</a>
                                                    @endif

                                                @elseif($reward->type == 'daily_poll')

                                                    @php($poll = App\Models\Poll::withTrashed()->find($reward->reference_id))
                                                    @if($poll)
                                                        <a href="{{ url('cms/vox/polls/edit/'.$poll->id) }}">{{ $poll->question }}</a>
                                                    @endif

                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            {!! $reward->reward !!}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>                    
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($total_pages > 1)
        <nav aria-label="Page navigation" style="text-align: center;">
            <ul class="pagination">
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link" href="{{ url('cms/users/rewards/?page=1'.$pagination_link) }}" aria-label="Previous">
                        <span aria-hidden="true"> << </span>
                    </a>
                </li>
                <li class="{{ ($page <= 1 ?  'disabled' : '' ) }}">
                    <a class="page-link prev" href="{{ url('cms/users/rewards/?page='.($page>1 ? $page-1 : '1').$pagination_link) }}"  aria-label="Previous">
                        <span aria-hidden="true"> < </span>
                    </a>
                </li>
                @for($i=$start; $i<=$end; $i++)
                    <li class="{{ ($i == $page ?  'active' : '') }}">
                        <a class="page-link" href="{{ url('cms/users/rewards/?page='.$i.$pagination_link) }}">{{ $i }}</a>
                    </li>
                @endfor
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link next" href="{{ url('cms/users/rewards/?page='.($page < $total_pages ? $page+1 :  $total_pages).$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> > </span> </a>
                </li>
                <li class="{{ ($page >= $total_pages ? 'disabled' : '') }}">
                    <a class="page-link" href="{{ url('cms/users/rewards/?page='.$total_pages.$pagination_link) }}" aria-label="Next"> <span aria-hidden="true"> >> </span>  </a>
                </li>
            </ul>
        </nav>
    @endif
@endsection