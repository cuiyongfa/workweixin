from distutils.core import setup


setup(
    name='workweixin',
    version='0.1',
    py_modules=['workweixin'],
    install_requires=['requests', 'fire', 'python-memcached'],
    url='https://github.com/supertaodi/workweixin',
    license='Apache',
    author='taodi',
    author_email='mlzgg@sina.cn',
    description='enterprise weixin api'
)
