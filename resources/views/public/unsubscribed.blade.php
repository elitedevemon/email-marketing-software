<!doctype html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Unsubscribed</title>
    <style>
      body {
        font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto;
        background: #0b0f17;
        color: #e6e9ef;
        margin: 0;
        padding: 40px;
      }

      .card {
        max-width: 560px;
        margin: 0 auto;
        background: #121a27;
        border: 1px solid rgba(255, 255, 255, .08);
        border-radius: 16px;
        padding: 24px;
      }

      .muted {
        color: rgba(230, 233, 239, .7);
        font-size: 14px;
        line-height: 1.6;
      }

      .badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        font-size: 12px;
      }
    </style>
  </head>

  <body>
    <div class="card">
      <h1 style="margin:0 0 10px 0; font-size:20px;">You’re unsubscribed</h1>
      <p class="muted" style="margin:0 0 16px 0;">
        We’ve added <span class="badge">{{ $masked }}</span> to our suppression list.
      </p>
      <p class="muted" style="margin:0;">
        It may take a short time for already-queued messages to stop, but new sends will be blocked.
      </p>
    </div>
  </body>

</html>
