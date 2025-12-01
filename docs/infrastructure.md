+------------------------------------------+
|            DigitalOcean Cloud            |
+------------------------------------------+

Droplet 1 (App Server)
- Laravel 12 API (PHP 8.x)
- Nginx
- Supervisor (if needed later)
- Queue worker (for notifications / logs)
- Serves admin API + mobile API

Next.js Admin App
- Deployed separately (Vercel or DO App Platform)
- Uses Sanctum SPA to talk to Laravel

Managed MySQL Database
- Primary storage for all LMS tables

Spaces (S3-compatible)
- PDF files
- Admin uploads

Bunny Stream
- Secure video hosting + streaming

