pack_files
==========

examples:

encrypt res/*.* to renews/, with specifies key (contain 16 chars max)
pack_files -i res -o resnew -ek XXTEA

encrypt res/*.* to renews/, with specifies key, filename extension
pack_files -i res -o resnew -ek XXTEA -ox dat(or .dat)


./pack_files.sh -i /Users/lansey/Desktop/test -o /Users/lansey/Desktop/1111 -ek lanseys1231 -ox dat
pack_files.bat -i C:/test/ -o C:/1111 -ek lanseys1231 -ox dat
