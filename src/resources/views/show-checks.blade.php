<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.css">
    <style>
        html {
            font-family: monospace;
            font-size: 16pt;
        }

        body {
            padding: 1em;
        }

        h1, h2 {
            color: dodgerblue;
            font-variant: small-caps;
            font-weight: normal;
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
</head>
<body>
<h1>
    {{ $checks->summarise() }}
</h1>

<hr>

<h2>Output</h2>

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

<hr>

<footer>
    {{ $checks->percentagePassed() }}% passed on {{ date('Y-m-d H:i:s') }}.
    Checked with
    <a href="https://github.com/brightmachine/scrutiny" rel="noopener" target="_blank">scrutiny</a> 👀
</footer>
</body>
</html>