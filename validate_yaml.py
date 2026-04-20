import yaml
s=open('.gitlab-ci.yml','r',encoding='utf-8').read()
try:
    yaml.safe_load(s)
    print('VALID')
except Exception as e:
    print('ERROR')
    print(e)
