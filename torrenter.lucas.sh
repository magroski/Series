for i  in {1..600000} 
do 
   wget http://seriando.com.br/reader/torrent -O - >> log.lucas; 
   sleep  $((RANDOM%6+4));
done
