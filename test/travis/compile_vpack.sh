#!/usr/bin/env bash
git clone --branch master https://github.com/martin-schilling/php-velocypack php-velocypack
cd php-velocypack
sh clone_velocypack
cd deps/velocypack
mkdir -p build
cd build
cmake .. -DCMAKE_INSTALL_PREFIX=/usr -DCMAKE_BUILD_TYPE=Release -DCMAKE_CXX_FLAGS="-fPIC -std=c++11"
sudo make install
echo $(pwd)
cd ../../../
echo $(pwd)
phpize
./configure
make all -j4
sudo make install