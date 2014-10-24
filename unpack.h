#ifndef _UNPACK_H_
#define _UNPACK_H_

#include <string>

class UnPackFiles
{
public:
    static unsigned char* unpack(const std::string& filename, const std::string& key, int* ret_len);
};

#endif // _UNPACK_H_