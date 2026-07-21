# Security notes (portfolio)

## Policy
- Keep the repo **private** until intentionally public.
- Never commit `.env`, tokens, or production DB passwords.
- Seed demo passwords are for local demo only.

## WMS
- Laravel `.env` is gitignored; copy from `.env.example`.
- Set `APP_DEBUG=false` and a strong `APP_KEY` before any public deploy.
- Spatie RBAC: Operator is floor-only (no master/PO/report admin).
- Demo logins in seeders/docs must be rotated if exposed publicly.

## If a secret was committed
1. Rotate immediately.
2. Rewrite history (`git filter-repo`) and force-push (private only).
3. Verify with `git log --all -- path`.
