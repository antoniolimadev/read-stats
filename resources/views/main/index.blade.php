@extends ('layouts.master')

@section('content')
    <div class="search-box">
        <form method="POST" action="{{ url('/userstats') }}" autocomplete="off" class="form-inline">
            @csrf
            @if(!$userDataArray)
                <input type="text" name="goodreads_id" id="userid" class="w3-input w3-border" placeholder="Enter your Goodreads user ID">
            @else
                <input type="text" name="goodreads_id" id="userid" class="w3-input w3-border" value="{{ $userDataArray['userId'] }}">
            @endif
            <input type="submit" value="Analyze" class="btn">
        </form>
    </div>
    <div class="form-inline credit">
        <div class="credit-text">All data from</div>
        <div class="credit-logo">
            <a href="https://www.goodreads.com">
                <img src="{{ url('/') . '/' . 'images/goodreads-logo.png' }}" width="197" height="43">
            </a>
        </div>
    </div>
    <br>
    @if($userDataArray)
        @include('main.stats')
    @endif
    @if($serverMessage)
        {{ $serverMessage }}
    @endif
    @if (count($errors))
            @foreach( $errors->all() as $error)
                {{ $error }} <br>
            @endforeach
    @endif
@endsection
