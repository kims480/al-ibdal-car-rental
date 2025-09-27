# al-ibdal-car-rental
Open Git Bash.
# Set your username:
   git config --global user.name "YourUsername"
# Set your email:
   git config --global user.email "YourEmail@example.com"
# Set your password:
   git config --global user.password "YourPassword"
# How Do I Check My Username and Email in Git Bash?
# To check your username:
  git config user.name
# To check your email:
  git config user.email
# Why Is Git Bash Asking for My Username and Password?
Git Bash prompts for your username and password to authenticate with a remote repository. This typically happens during actions like pushing or pulling changes.

Where Can I Find My Git Password?
Git passwords are stored securely and are not directly accessible. However, you can 
# reset or configure your password using:

git config --global user.password "YourPassword"
# How Do I Configure Username and Password Globally in Git Bash?
Open Git Bash.
Set your username:
   git config --global user.name "YourUsername"
Set your email:
   git config --global user.email "YourEmail@example.com"
Set your password:
   git config --global user.password "YourPassword"

# 🛠 Solutions
# Option 1: Keep your local .env (most common)

Rename or move it temporarily:

mv web-frontend/.env web-frontend/.env.local
git pull origin main
mv web-frontend/.env.local web-frontend/.env


Now you have the remote .env and your local copy side by side — compare them and merge manually.

# Option 2: Discard your local .env

If you don’t need your local .env (⚠️ destructive):

rm web-frontend/.env
git pull origin main

# Option 3: Tell Git to keep your local version

If you always want to keep your .env and ignore changes from remote:

git update-index --assume-unchanged web-frontend/.env
git pull origin main


This way, Git will stop bothering you about that file.
(But careful: you won’t get updates to .env from GitHub anymore.)

# Option 4: Stash even untracked files

If you want to stash your local .env too:

git stash --include-untracked
git pull origin main
git stash pop
