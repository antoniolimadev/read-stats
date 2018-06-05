<div class="user-stats">
    <!-- USER STATS -->
    <div class="user-stats">
        <div class="personal-info">
            <div class="avatar"><img src={{ $userDataArray['userAvatarUrl'] }}></div>
            <h1>{{ $userDataArray['userName'] }}</h1>
            <div>Joined in {{ $userDataArray['joinDate'] }}</div>
        </div>
        <div class="block-container">
            <div class="block card-single">
                <div>
                    <div class="card-title">Books read</div>
                    <div class="number-stat"> {{ $userDataArray['booksRead'] }} </div>
                    <div class="card-title">Your average rating</div>
                    <div class="number-stat">{{ $userDataArray['averageUserRating'] }}</div>
                </div>
            </div>
            <div class="block card-single">
                <div>
                    <div class="card-title">Highest rated</div>
                    <div class="block-container book-entry">
                        <div class="book-cover">
                            <a href="{{ $userDataArray['highestRatedBook']->websiteURL }}" target="_blank">
                                <img src="{{ $userDataArray['highestRatedBook']->coverURL }}"> </a>
                        </div>
                        <div class="book-info">
                            @if (strlen($userDataArray['highestRatedBook']->title) > $userDataArray['maxStringSize'])
                                <div class="tooltip">
                                    {{--book title shortened--}}
                                    <a class="book-title" href="{{ $userDataArray['highestRatedBook']->websiteURL }}" target="_blank">
                                        {{ substr($userDataArray['highestRatedBook']->title, 0, $userDataArray['maxStringSize']) }}...
                                    </a>
                                    {{--tooltip with full book title--}}
                                    <span class="tooltiptext">{{ $userDataArray['highestRatedBook']->title }}</span>
                                </div>
                            @else
                                <a class="book-title" href="{{ $userDataArray['highestRatedBook']->websiteURL }}" target="_blank">
                                    {{ $userDataArray['highestRatedBook']->title }}
                                </a>
                            @endif
                            <div class="book-stat">{{ $userDataArray['highestRatedBook']->averageRating }} stars</div>
                        </div>
                    </div>
                    <div class="card-title">Lowest rated</div>
                    <div class="block-container book-entry">
                        <div class="book-cover">
                            <a href="{{ $userDataArray['lowestRatedBook']->websiteURL }}" target="_blank">
                                <img src="{{ $userDataArray['lowestRatedBook']->coverURL }}"> </a>
                        </div>
                        <div class="book-info">
                            @if (strlen($userDataArray['lowestRatedBook']->title) > $userDataArray['maxStringSize'])
                                <div class="tooltip">
                                    {{--book title shortened--}}
                                    <a class="book-title" href="{{ $userDataArray['lowestRatedBook']->websiteURL }}" target="_blank">
                                        {{ substr($userDataArray['lowestRatedBook']->title, 0, $userDataArray['maxStringSize']) }}...
                                    </a>
                                    {{--tooltip with full book title--}}
                                    <span class="tooltiptext">{{ $userDataArray['lowestRatedBook']->title }}</span>
                                </div>
                            @else
                                <a class="book-title" href="{{ $userDataArray['lowestRatedBook']->websiteURL }}" target="_blank">
                                    {{ $userDataArray['lowestRatedBook']->title }}
                                </a>
                            @endif
                            <div class="book-stat">{{ $userDataArray['lowestRatedBook']->averageRating }} stars</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="block card-single">
                <div>
                    <div class="card-title">Average time per book</div>
                    <div class="number-stat"> {{ $userDataArray['meanTime'] }} days</div>
                    <div class="card-title">Average pages per book</div>
                    <div class="number-stat"> {{ $userDataArray['averagePages'] }} </div>
                </div>
            </div>
        </div>
        <br/>
    </div> {{-- end of user stats --}}
    <!-- GRAPH -->
    <div class="block-container">
        <div class="card-graph">
            <table class="q-graph" style="width:{{ $userDataArray['graphWidth'] }}">
                <caption class="card-title" style="width:{{ $userDataArray['graphWidth'] }}">Books read per year</caption>
                <tbody>
                @php
                    $left = 0; /* distance between bars */
                    $i = 0;
                @endphp
                @foreach($userDataArray['booksReadPerYear'] as $year)
                    <tr style="left: {{ $left }}px;">
                        <th scope="row"> {{ $year[0] }} </th>
                        <td class="paid bar" style="height: {{ $userDataArray['heightArray'][$i++] }}px;">
                            {{ $year[1] }}
                        </td>
                    </tr>
                    @php $left = $left + 80; @endphp
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    {{-- RANKINGS --}}
    <br/>
    <br/>
    <div class="block-container">
        <div class="block card">
            <div>
                <div class="card-title">Most read authors</div>
                @php $index = 1; @endphp
                @foreach($userDataArray['authorsFrequency'] as $value)
                    <div class="block-container author-entry">
                        <div class="book-rank"><span class="ranking-badge"> {{ $index++ }} </span></div>
                        <div class="book-cover">
                            <a href={{ $value->authorURL }} target="_blank"> <img src="{{ $value->authorPhotoURL }}"> </a>
                        </div>
                        <div class="book-info">
                            <a class="book-title" href="{{ $value->authorURL }}" target="_blank">{{ $value->name }}</a>
                            <div class="book-stat">{{ $value->booksRead }} books </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="block card">
            <div>
                <div class="card-title">Fastest reads</div>
                @if($userDataArray['fastestBooks'])
                    @php $index = 1; // array_key_exists('index' , $viewData) ? $viewData['index'] : 1; @endphp
                    @foreach($userDataArray['fastestBooks'] as $book)
                        <div class="block-container book-entry">
                            <div class="book-rank"><span class="ranking-badge"> {{ $index++ }} </span></div>
                            <div class="book-cover">
                                <a href={{ $book->websiteURL }} target="_blank"> <img src="{{ $book->coverURL }}"> </a>
                            </div>
                            <div class="book-info">
                                @if (strlen($book->title) > $userDataArray['maxStringSize'])
                                <div class="tooltip">
                                    {{--book title shortened--}}
                                    <a class="book-title" href="{{ $book->websiteURL }}" target="_blank">
                                        {{ substr($book->title, 0, $userDataArray['maxStringSize']) }}...
                                    </a>
                                    {{--tooltip with full book title--}}
                                    <span class="tooltiptext">{{ $book->title }}</span>
                                </div>
                                @else
                                    <a class="book-title" href="{{ $book->websiteURL }}" target="_blank">{{ $book->title }}</a>
                                @endif
                                <div class="book-stat">{{ $book->timeToRead }} days </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div>Why no books?</div>
                @endif
            </div>
        </div>
        <div class="block card">
            <div>
                <div class="card-title">Slowest reads</div>
                @if($userDataArray['slowestBooks'])
                    @php $index=1; @endphp
                    @foreach($userDataArray['slowestBooks'] as $book)
                        <div class="block-container book-entry">
                            <div class="book-rank"><span class="ranking-badge"> {{ $index++ }} </span></div>
                            <div class="book-cover">
                                <a href={{ $book->websiteURL }} target="_blank"> <img src="{{ $book->coverURL }}"> </a>
                            </div>
                            <div class="book-info">
                                @if (strlen($book->title) > $userDataArray['maxStringSize'])
                                <div class="tooltip">
                                    {{--book title shortened--}}
                                    <a class="book-title" href="{{ $book->websiteURL }}" target="_blank">
                                        {{ substr($book->title, 0, $userDataArray['maxStringSize']) }}...
                                    </a>
                                    {{--tooltip with full book title--}}
                                    <span class="tooltiptext">{{ $book->title }}</span>
                                </div>
                                @else
                                    <a class="book-title" href="{{ $book->websiteURL }}" target="_blank">{{ $book->title }}</a>
                                @endif
                                <div class="book-stat">{{ $book->timeToRead }} days </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div>Why no books?</div>
                @endif
            </div>
        </div>
    </div>
</div>

