<!-- resources/views/upload.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Excel Upload</title>
</head>
<body>

@if(Session::has('success'))
    <p>{{ Session::get('success') }}</p>
@endif

<form action="{{ url('/upload-excel') }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file">
    <button type="submit">Upload</button>
</form>

</body>
</html>