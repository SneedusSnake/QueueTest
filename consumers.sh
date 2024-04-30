for i in $(seq 1 $1)
do
    ./application.php Consume &
done

wait





