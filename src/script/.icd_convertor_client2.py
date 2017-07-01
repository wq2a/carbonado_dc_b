#!/usr/bin/env python
from libs.option import opt
from libs.multi import procs
from libs.db.mydb import Mydb
from libs.myutils.parser2 import Parser
from libs.myutils.wget import Wget
from libs.myutils.writer import Writer
from libs.myutils import utils
from random import randint
import os
import re
import time
import json
import urllib

def main():
    options = opt.getArgs()
    opt.verify(options,[])
    pkt = procs.buildPKT(eav2Info, eavEntity2Data(options), options)
    procs.joinme(pkt)
    exit(1)

def eavEntity2Data(opts):
    sql = "select str_alias_lower from ICD910CM_STR_ALIAS where icd910cm_str_id_by_alias is NULL \
        and icd910cm_str_id is NULL and icd910cm_icd9_by_wiki is NULL \
        and icd910cm_icd10_by_wiki is NULL " 
    mydb = Mydb(opts)
    data = mydb.fetch(sql)
    mydb.close()
    return data

def eav2Info(pid, data, opts):
    for d in data:
        print d[0]
        url = 'http://dev.cb.com/api/icd_convertor/list?'+urllib.urlencode({'source': d[0]})
        print url
        print utils.request(url)

    exit(1)

#./test.py --dbhost 
if __name__ == '__main__':
    main()
