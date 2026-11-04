<div style="font-family: system-ui, sans-serif; color: #0c2a45; max-width: 560px">
    <p style="font-size: 18px; font-weight: 700; margin: 0 0 12px">New enquiry from your portfolio</p>
    <p style="margin: 0 0 4px"><strong>{{ $m->name }}</strong> &lt;{{ $m->email }}&gt;</p>
    @if ($m->company)<p style="margin: 0 0 4px">Company: {{ $m->company }}</p>@endif
    @if ($type)<p style="margin: 0 0 4px">Project: {{ $type }}</p>@endif
    @if ($budget)<p style="margin: 0 0 4px">Budget: {{ $budget }}</p>@endif
    <div style="margin-top: 16px; padding: 14px; background: #f5f7f8; border-radius: 8px; white-space: pre-wrap">{{ $m->body }}</div>
    <p style="margin-top: 16px; font-size: 13px; color: #5a6b7b">Reply to this email to answer them directly. It is also saved under Messages in the portfolio admin.</p>
</div>
