#!/bin/sh

if [ -z "$1" ]
then

cat << EOF
Please specify the tag or branch to make a release of.

Examples:
    
    sh make_release.sh 0.1.0
    sh make_release.sh master
    sh make_release.sh develop

EOF
exit 1
else
    TAG=$1
fi


# get Composer
(
cd /tmp
curl -O http://getcomposer.org/composer.phar
)

# clone the tag

(
cd /tmp
git clone -b $TAG https://github.com/fkooman/ssp-voot-groups.git
cd ssp-voot-groups
php /tmp/composer.phar install
rm composer.json composer.lock
rm -rf .git
rm -f .gitignore
tar -czf ssp-voot-groups-$TAG.tar.gz ssp-voot-groups
)
