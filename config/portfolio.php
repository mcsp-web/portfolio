<?php

return [
    // Where admin uploads go. Use "s3" when a MinIO bucket is attached in Coolify.
    'upload_disk' => env('PORTFOLIO_UPLOAD_DISK', 'public'),
];
