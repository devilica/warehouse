<!DOCTYPE html>
<html>
<head><title>{{ $type }} Report</title></head>
<body>
<h1>{{ ucfirst(str_replace('-', ' ', $type)) }} Report</h1>
<p>Generated at {{ now() }}</p>
</body>
</html>