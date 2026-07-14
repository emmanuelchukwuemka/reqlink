@echo off
echo [1/3] Adding changes to Git...
git add .
echo [2/3] Committing changes...
git commit -m "Make email compulsory on signup"
echo [3/3] Deploying to cPanel...
python deploy.py
echo.
echo Deployment process complete.
pause
