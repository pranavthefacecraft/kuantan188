# GitHub Actions Deployment Setup

## 🚀 Automatic Deployment Configuration

This repository is configured for automatic deployment to your subdomains:

- **Backend changes** → `admin.tfcmockup.com`
- **Frontend changes** → `tickets.tfcmockup.com`

## 📋 Required GitHub Secrets

You need to add these secrets in your GitHub repository settings:

### Go to: GitHub Repository → Settings → Secrets and variables → Actions → New repository secret

### **FTP/Server Credentials:**
```
FTP_HOST=your-ftp-host (e.g., ftp.tfcmockup.com)
FTP_USERNAME=your-ftp-username
FTP_PASSWORD=your-ftp-password
SSH_HOST=your-ssh-host (same as FTP_HOST usually)
SSH_USERNAME=your-ssh-username  
SSH_PASSWORD=your-ssh-password
```

### **Database & Laravel:**
```
DB_USERNAME=your-database-username
DB_PASSWORD=your-database-password
APP_KEY=base64:your-32-character-app-key
```

## 🔧 How to Generate APP_KEY

Run this command locally and copy the key:
```bash
cd backend
php artisan key:generate --show
```

## 📁 Deployment Triggers

### **Backend Deployment:**
- Triggers when you push changes to `backend/` folder
- Automatically runs Laravel optimizations
- Uploads to `admin.tfcmockup.com`

### **Frontend Deployment:**  
- Triggers when you push changes to `frontend/` folder
- Builds React production bundle
- Uploads to `tickets.tfcmockup.com`

## 🧪 Testing Deployment

1. Make a small change to `backend/README.md`
2. Commit and push to GitHub
3. Check GitHub Actions tab to see deployment progress
4. Verify changes appear on `admin.tfcmockup.com`

## 🔒 Security Notes

- Never commit `.env` files with real credentials
- All sensitive data is stored as GitHub secrets
- Production builds automatically exclude development files

## 📊 Deployment Status

You can monitor deployments in:
- GitHub Repository → Actions tab
- Real-time logs and deployment status
- Automatic rollback on failure