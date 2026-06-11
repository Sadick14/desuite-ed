# SSL Certificates

Place your SSL certificate and key files here for production use!

Files expected:
- `cert.pem`: SSL certificate (full chain)
- `privkey.pem`: Private key

For Let's Encrypt, you can copy them from:
```bash
cp /etc/letsencrypt/live/your-domain.com/fullchain.pem ./cert.pem
cp /etc/letsencrypt/live/your-domain.com/privkey.pem ./privkey.pem
```
