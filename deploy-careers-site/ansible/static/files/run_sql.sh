##
# runsql.sh
##
# Simple script that will loop through sql files in a given directory and
# apply them one by one.
#
# usage: ./run_sql.sh <db user> <db password> <environment>

set -eo pipefail

for sqlfile in $(ls -1 *.sql); do
  echo ${sqlfile}
  mysql -v -u ${1} -p${2} -h careers-site-db.${3}.cshr-gov.uk wordpress < ${sqlfile} 2>&1 > ${sqlfile}.log
done
