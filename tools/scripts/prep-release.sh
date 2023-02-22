current_dir="$(basename "$PWD")"

if [ -n "$(git status --porcelain)" ]; then
  echo "You have uncommitted changes in your working tree. Please stash them and try again."
  exit 1;
fi

echo "Checking out main and pulling in latest changes..."
git checkout main && git pull origin main

if [ -d "vendor" ]; then
  rm -rf vendor/
fi

echo "Installing dependencies without dev..."
composer install --no-dev

echo "Creating module archive for submission to Magento..."
cd ..
mkdir -p PennyBlack/PennyBlack
rsync -av --exclude=".idea" --exclude=".DS_Store" --exclude=".git" magento-app/ PennyBlack/PennyBlack

echo "Creating zipped archive "
zip -r pennyblack.zip PennyBlack -x ".*"

mv pennyblack.zip "${current_dir}"/

rm -rf PennyBlack
cd "${current_dir}" || exit