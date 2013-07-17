#!/bin/sh

RELEASE_DIR=${HOME}/Releases
GITHUB_USER=fkooman
PROJECT_NAME=ssp-voot-groups
TARGET_DIR=vootgroups

if [ -z "$1" ]
then

cat << EOF
Please specify the tag or branch to make a release of.

Examples:
    
    sh make_release.sh 0.1.0
    sh make_release.sh master
    sh make_release.sh develop

If you want to GPG sign the release, you can specify the "sign" parameter, this will
invoke the gpg command line tool to sign it.

   sh make_release 0.1.0 sign

EOF
exit 1
else
    TAG=$1
fi

mkdir -p ${RELEASE_DIR}
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}

# get Composer
(
cd ${RELEASE_DIR}
curl -O http://getcomposer.org/composer.phar
)

# clone the tag
(
cd ${RELEASE_DIR}
git clone -b ${TAG} https://github.com/${GITHUB_USER}/${PROJECT_NAME}.git
)

# run Composer
(
cd ${RELEASE_DIR}/${PROJECT_NAME}
php ${RELEASE_DIR}/composer.phar install
)

# remove Git and Composer files
(
rm -rf ${RELEASE_DIR}/${PROJECT_NAME}/.git
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/.gitignore
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/composer.json
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/composer.lock
rm -f ${RELEASE_DIR}/${PROJECT_NAME}/make_release.sh
)

# create tarball
(
cd ${RELEASE_DIR}
mv ${PROJECT_NAME} ${TARGET_DIR}
tar -czf ${PROJECT_NAME}-${TAG}.tar.gz ${TARGET_DIR}
)

# create checksum file
(
cd ${RELEASE_DIR}
shasum ${PROJECT_NAME}-${TAG}.tar.gz > ${PROJECT_NAME}.sha
)

# sign it if requested
(
if [ -n "$2" ]
then
	if [ "$2" == "sign" ]
	then
		cd ${RELEASE_DIR}
		gpg -o ${PROJECT_NAME}.sha.gpg  --clearsign ${PROJECT_NAME}.sha
	fi
fi
)
