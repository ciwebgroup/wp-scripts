# The Mission

Add security middleware to each and every server's traefik configuration, and to all respective sites on the server.


## The Process

### For Each Server

- `ssh root@wp#.ciwgserver.com`
- `cd /var/opt/traefik`
- `nano ./docker-compose.yml`
- Delete all of the old labels
- Paste all labels from `traefik.yml` in this repository
- Update the host # from wp0 to the correct host
  - - "traefik.http.routers.api.rule=Host(\`monitor.wp#.ciwgserver.com\`)"
- Save and Exit
- `dc down && dc up -d`
- Configure each domain


### For Each Domain

- `cd /var/opt/`
- `ls`
- `cd domain.com`
- `nano ./docker-compose.yml`
- Modify the middleware line to include `wordpress-security` (note: you may have to remove `block-sql-files`)
- Save and Exit
- `dc down && dc up -d`
- Check Website
- Repeat


## Notes / Final Thoughts

1. Check your work by logging into [Traefik](https://monitor.wp0.ciwgserver.com)
2. If you're feeling really bold, feel free to do bash shell command like:
```
for DFILE in `grep -l block-sql-files */docker-compose.yml`; do echo sed -i 's|block-sql-files|wordpress-security|g' $DFILE; dc down; dc up -d; done;
```
