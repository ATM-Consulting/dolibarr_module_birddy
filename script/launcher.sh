#! /bin/sh

NAME=birddydaemon
DESC="Daemon chat server"
DAEMONUSER="www-data"

#DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
DIR=$2
PIDFILE="${DIR}/run/${NAME}.pid"
LOGFILE="${DIR}/log/${NAME}.log"

DAEMON="/usr/bin/php"
DAEMON_OPTS="${DIR}/daemon.php"

START_OPTS="--start --chuid ${DAEMONUSER} --background --make-pidfile --pidfile ${PIDFILE} --exec ${DAEMON} ${DAEMON_OPTS}"
STOP_OPTS="--stop --pidfile ${PIDFILE}"

test -x $DAEMON || exit 0

set -e

case "$1" in
    start)
        echo -n "birddy_start_server"
        start-stop-daemon $START_OPTS >> $LOGFILE
        ;;
    stop)
        echo -n "birddy_stop_server"
        start-stop-daemon $STOP_OPTS
        rm -f $PIDFILE
        ;;
    restart|force-reload)
        echo -n "birddy_restart_server"
        start-stop-daemon $STOP_OPTS
        sleep 1
        start-stop-daemon $START_OPTS >> $LOGFILE
        ;;
    *)
        #N=/etc/init.d/$NAME
        echo "Usage: sh launch.sh {start|stop|restart|force-reload}" >&2
        exit 1
        ;;
esac

exit 0
