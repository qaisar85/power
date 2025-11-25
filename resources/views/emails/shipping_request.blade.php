<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Shipping Request</title>
</head>
<body style="font-family: Arial, sans-serif;">
  <h2>New Shipping Request</h2>
  <p><strong>Listing:</strong> {{ $listing->title }} (ID: {{ $listing->id }})</p>
  <p><strong>Category:</strong> {{ $listing->category }} {{ isset($listing->subcategories) && is_array($listing->subcategories) && count($listing->subcategories) ? ' / ' . implode(', ', $listing->subcategories) : '' }}</p>
  <p><strong>Location:</strong> {{ $listing->location }}</p>
  <hr>
  <p><strong>Requester:</strong> {{ $requester->name }} ({{ $requester->email }})</p>
  @if($message)
    <p><strong>Message:</strong> {{ $message }}</p>
  @endif
  @if(!empty($meta))
    <p><strong>Details:</strong></p>
    <pre style="background:#f7f7f7;padding:8px;border:1px solid #eee;">{{ json_encode($meta, JSON_PRETTY_PRINT) }}</pre>
  @endif
  <hr>
  <p>This email was generated automatically. Please follow up with the requester to provide shipping cost and logistics options.</p>
</body>
</html>