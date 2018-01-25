mkdir iati-schemas
cd iati-schemas
for v in 1.01 1.02 1.03 1.04 1.05 2.01 2.02 2.03; do
    git clone https://github.com/IATI/IATI-Schemas.git $v
    cd $v
    git checkout version-$v
    git pull
    cd ..
done
