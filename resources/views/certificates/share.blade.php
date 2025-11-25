<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shareholder Certificate</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111; }
        .container { width: 700px; margin: 0 auto; padding: 40px; border: 2px solid #333; }
        .title { text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 20px; }
        .subtitle { text-align: center; font-size: 14px; color: #666; margin-bottom: 30px; }
        .section { margin-bottom: 18px; }
        .label { font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #555; }
        .sig { margin-top: 40px; display: flex; justify-content: space-between; }
        .sig .line { width: 280px; border-bottom: 1px solid #999; height: 24px; }
    </style>
</head>
<body>
<div class="container">
    <div class="title">Shareholder Certificate</div>
    <div class="subtitle">World Oil Project — Ownership of WOP Shares</div>

    <div class="section">
        <span class="label">Certificate No:</span>
        C-{{ str_pad($tx->id, 8, '0', STR_PAD_LEFT) }}
    </div>
    <div class="section">
        <span class="label">Holder:</span>
        {{ $user->name }} (ID: {{ $user->id }})
    </div>
    <div class="section">
        <span class="label">Email:</span>
        {{ $user->email }}
    </div>
    <div class="section">
        <span class="label">Shares:</span>
        {{ $tx->shares }}
    </div>
    <div class="section">
        <span class="label">Price per Share:</span>
        ${{ number_format($tx->price_per_share, 2) }} USD
    </div>
    <div class="section">
        <span class="label">Total Amount:</span>
        ${{ number_format($tx->amount, 2) }} USD
    </div>
    <div class="section">
        <span class="label">Transaction Reference:</span>
        {{ $tx->payment_reference }}
    </div>
    <div class="section">
        <span class="label">Issued At:</span>
        {{ $issued_at->format('Y-m-d H:i:s') }}
    </div>

    <div class="sig">
        <div>
            <div class="label">Authorized Signature</div>
            <div class="line"></div>
        </div>
        <div>
            <div class="label">Date</div>
            <div class="line"></div>
        </div>
    </div>

    <div class="footer">
        This certificate acknowledges the ownership of WOP shares by the holder named above. 
        It is issued subject to the terms of the Investment Agreement. This is not a public offer.
    </div>
</div>
</body>
</html>