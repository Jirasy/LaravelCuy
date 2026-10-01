<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>mapel yang di ampu</h1>
    @foreach ($mapel as $teacher)
    <h1>{{ $teacher->tcr_name }}</h1>
    <P>{{ $teacher->tcr_subject }}</P>
    <p>{{ $teacher->tcr_total_time }} jam</p>
    @endforeach
</body>
</html>