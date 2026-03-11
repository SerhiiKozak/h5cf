# h5cf

## Run project

```bash
git clone https://github.com/SerhiiKozak/h5cf.git
cd h5cf
docker compose up -d

API:
GET http://localhost:8080/api/v1/health
Header:
X-Owner: {uuid}
