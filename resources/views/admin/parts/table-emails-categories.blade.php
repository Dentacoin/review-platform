@if(!empty($item->subscribe_category))
    {{ config('email-categories')[$item->subscribe_category] }}
@endif