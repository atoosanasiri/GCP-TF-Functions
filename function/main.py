# Cloud Function to load telemetry data from bucket 
# Trigger by file arrive on bucket

import csv
import os
import sqlalchemy
from google.cloud import storage

storage_client = storage.Client()

# Set the following variables depending on your specific
# connection name and root password from the earlier steps:


def loadscsvsql_fromgcs(event, context):

    db_user = os.environ["DB_USER"]
    db_pass = os.environ["DB_PASS"]
    db_name = os.environ["DB_NAME"]
    db_host = os.environ["DB_HOST"]

    # Extract host and port from db_host
    host_args = db_host.split(":")
    db_hostname, db_port = host_args[0], int(host_args[1])

    db = sqlalchemy.create_engine(
        # Equivalent URL:
        # postgres+pg8000://<db_user>:<db_pass>@<db_host>:<db_port>/<db_name>
        sqlalchemy.engine.url.URL(
            drivername="postgres+pg8000",
            username=db_user,  # e.g. "my-database-user"
            password=db_pass,  # e.g. "my-database-password"
            host=db_hostname,  # e.g. "127.0.0.1"
            port=db_port,  # e.g. 5432
            database=db_name  # e.g. "my-database-name"
        ),
        pool_size=2,
        max_overflow=2,
        pool_timeout=30,
        pool_recycle=1800
        # ... Specify additional properties here.
        # [END cloud_sql_postgres_sqlalchemy_create_tcp]
        # **db_config
        # [START cloud_sql_postgres_sqlalchemy_create_tcp]
    )

    blob = storage_client.get_bucket(event['bucket']).get_blob(event['name'])
    rows = blob.download_as_text()
    lines = rows.splitlines()
    reader = csv.reader(lines)
    try:
        with db.connect() as conn:
            #conn.execute(stmt,out)
            for line in reader:
                if (line[0].lower() != 'event_ts_utc'):
                    stmt = "INSERT INTO GMLC_T_LOCATION_TRACKINGV2 VALUES ('{}','{}',{},{},{},{},{},{},{},'{}',{},'{}','{}','{}','{}')".format(line[0],line[1],line[14],line[2],line[3],line[4],line[5],line[6],line[7],line[8],line[9],line[10],line[11],line[12],line[13])
                    #print(stmt)
                    try:
                        conn.execute(sqlalchemy.text(stmt))
                    except Exception as e:
                        print('Error: {}'.format(str(e)))
            blob.delete()
        conn.close()
    except Exception as e:
        print('Error: {}'.format(str(e)))
    finally:
        if conn is not None:
            conn.close()
    # print(parsed_csv)

# [END of loadscsvsql_fromgcs]

