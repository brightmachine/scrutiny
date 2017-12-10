<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Scrutiny</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.css">
    <style>
        html {
            font-family: monospace;
            font-size: 16pt;
        }

        body {
            padding: 1em;
        }

        h1, h2, h3 {
            color: dodgerblue;
            font-variant: small-caps;
            font-weight: normal;
        }

        article h1 {
            font-size: 1.5em;
        }

        article h2 {
            font-size: 1.17em;
            color: darkslategrey;
        }

        h3 {
            color: darkslategrey;
        }

        ul.checks {
            list-style: none;
            padding: 0;
        }

        ul.checks li {
            display: table-cell;
            font-size: 1.5em;
        }

        ul.failed, ul.skipped {
            list-style: none;
            padding-left: 2.5em;
            text-indent: -1em;
        }

        ul.failed {
            border-left: 5px double red;
        }

        ul.skipped {
            border-left: 5px double lightgrey;
        }

        ul.failed li:before {
            content: "💩 ";
        }

        ul.skipped li:before {
            content: "🙈 ";
        }

        li {
            margin: .65em 0;
        }

        hr {
            border-top: dashed 1px plum;
            margin: 2em 0;
        }

        footer {
            color: #ccc;
        }
    </style>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
</head>
<body>
<h1>
    Scrutiny
</h1>

<hr>
<article>
    <h1>⏳ Last check</h1>
    <h2>{{ $checks->summarise() }}</h2>

    <ul class="checks">
        @foreach($checks->all() as $value)
            <li>{{ $value['icon'] }}</li>
        @endforeach
    </ul>

    @if($checks->failed()->count())
        <p>{{ $checks->failed()->count() }} failed</p>
        <ul class="failed">
            @foreach($checks->failed() as $value)
                <li>{{ $value['name'] }}: {{ $value['message'] }}</li>
            @endforeach
        </ul>
    @endif

    @if($checks->skipped()->count())
        <p>{{ $checks->skipped()->count() }} skipped</p>
        <ul class="skipped">
            @foreach($checks->skipped() as $value)
                <li>{{ $value['name'] }}: {{ $value['message'] }}</li>
            @endforeach
        </ul>
    @endif

    <footer>
        {{ $checks->percentagePassed() }}% passed on {{ date('Y-m-d H:i:s', $checks->time()) }}.
        Checked with
        <a href="https://github.com/brightmachine/scrutiny" rel="noopener" target="_blank">scrutiny</a> 👀
    </footer>
</article>
<hr>
<h2>⌛ History</h2>

@forelse($historyByProbe as $k => $history)
    <h3>{{ $history->last()['name'] }}</h3>
    @if($history->mixedOrMissingMeasurements())
        @include('scrutiny::charts.timeline', compact('history'))
    @elseif($history->percentageMeasurements())
        @include('scrutiny::charts.percentage', compact('history'))
    @elseif($history->durationMeasurements())
        @include('scrutiny::charts.duration', compact('history'))
    @endif

    <hr>
@empty
    <em>No history found</em>

    <hr>
@endforelse

</body>
</html>