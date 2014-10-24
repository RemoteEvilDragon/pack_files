#include "unpack.h"
#include "cocos2d.h"

extern "C" {
#include "xxtea.h"
}

USING_NS_CC;

unsigned char* UnPackFiles::unpack(const std::string& filename, const std::string& key, int* ret_len)
{
    if (filename.empty() || key.empty()) {
        if (ret_len) 
            *ret_len = -1;
        return nullptr;
    }

    std::string path = CCFileUtils::sharedFileUtils()->fullPathForFilename(filename);
    Data data = FileUtils::sharedFileUtils()->getDataFromFile(path);

    xxtea_long len;
    unsigned char* result = xxtea_decrypt(data.getBytes(), (xxtea_long)data.getSize(), 
        (unsigned char*)key.c_str(), (xxtea_long)key.size(), &len);
    if (ret_len)
        *ret_len = (int)len;
    return result;
}
