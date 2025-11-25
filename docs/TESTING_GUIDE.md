# Testing Guide - Priority 1 Features

## Quick Start Testing

### 1. Setup
```bash
# Run migrations
php artisan migrate

# Run seeders (creates roles and initial data)
php artisan db:seed

# Create storage link for file uploads
php artisan storage:link

# Build frontend assets
npm run build
# OR for development
npm run dev
```

### 2. Configure OAuth (Optional but Recommended)

Add to `.env`:
```env
GOOGLE_CLIENT_ID=your_google_client_id
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URL=http://localhost:8000/auth/google/callback

LINKEDIN_CLIENT_ID=your_linkedin_client_id
LINKEDIN_CLIENT_SECRET=your_linkedin_client_secret
LINKEDIN_REDIRECT_URL=http://localhost:8000/auth/linkedin/callback

FACEBOOK_CLIENT_ID=your_facebook_client_id
FACEBOOK_CLIENT_SECRET=your_facebook_client_secret
FACEBOOK_REDIRECT_URL=http://localhost:8000/auth/facebook/callback
```

**OAuth Setup Guides:**
- Google: https://console.cloud.google.com/apis/credentials
- LinkedIn: https://www.linkedin.com/developers/apps
- Facebook: https://developers.facebook.com/apps

---

## Testing Scenarios

### Scenario 1: New User Registration Flow

1. **Visit Registration Page**
   - Go to `/register`
   - Verify OAuth buttons are visible (Google, LinkedIn, Facebook)

2. **Register with Email**
   - Fill in email, password, confirm password
   - Submit form
   - Should redirect to role selection page

3. **Select Role**
   - Choose a role (e.g., "Branch")
   - Submit selection
   - Should redirect to dashboard

4. **Verify Dashboard**
   - Check role-based quick actions appear
   - Verify statistics widgets are displayed
   - Check navigation works

---

### Scenario 2: OAuth Login Flow

1. **Click OAuth Button**
   - Click "Sign in with Google" (or LinkedIn/Facebook)
   - Should redirect to OAuth provider

2. **Authorize Application**
   - Complete OAuth authorization
   - Should redirect back to application

3. **Role Selection**
   - If first time, should see role selection
   - Select role and continue

4. **Dashboard Access**
   - Should land on dashboard
   - Verify user is logged in

---

### Scenario 3: Regional Agent Profile Management

1. **Login as Branch Role**
   - Register/login and select "Branch" role
   - Go to Dashboard

2. **Access Profile Page**
   - Click "My Profile" in quick actions
   - Should navigate to `/regional-agent/profile`

3. **Upload Logo**
   - Click "Upload Logo"
   - Select an image file (JPG, PNG, WebP)
   - Verify preview appears
   - File should be under 5MB

4. **Upload Video Resume**
   - Click "Upload Video Resume"
   - Select a video file (MP4, WebM, OGG)
   - Verify preview appears
   - File should be under 100MB

5. **Fill Profile Information**
   - Enter business name
   - Enter business description
   - Select region type (City/State/Country/Global)
   - Select country (if applicable)
   - Select state (if applicable)
   - Select city (if applicable)
   - Enter latitude/longitude (optional)
   - Select service types
   - Add languages
   - Enter office address, phone, email
   - Enter timezone

6. **Save Profile**
   - Click "Save Profile"
   - Verify success message
   - Check profile is saved

7. **Verify on Contacts Page**
   - Navigate to `/contacts`
   - Verify agent appears on 3D globe
   - Click on agent marker
   - Verify profile details display
   - Check video resume plays

---

### Scenario 4: Contacts Page Testing

1. **Visit Contacts Page**
   - Go to `/contacts`
   - Verify 3D globe loads
   - Check agent markers appear

2. **Test Filtering**
   - Filter by country
   - Filter by language
   - Search by manager name
   - Verify results update

3. **View Agent Profile**
   - Click on agent marker on globe
   - OR click on agent card in sidebar
   - Verify agent details display
   - Check video resume plays
   - Verify contact information

4. **Navigate to Full Profile**
   - Click "View Full Profile"
   - Verify full profile page loads
   - Check all information displays correctly

---

### Scenario 5: Main Page Testing

1. **Visit Home Page**
   - Go to `/`
   - Verify video banner plays
   - Check search bar is visible

2. **Test Statistics**
   - Scroll to statistics section
   - Verify counters animate
   - Check numbers are displayed correctly

3. **Test Search**
   - Enter search term
   - Select country
   - Select category
   - Submit search
   - Verify redirects to marketplace

4. **Check Latest Products**
   - Scroll to "Latest Products" section
   - Verify products display
   - Check product cards are clickable

5. **Check Latest Auctions**
   - Scroll to "Latest Auctions" section
   - Verify auctions display
   - Check countdown timers work

---

## Common Issues & Solutions

### Issue: OAuth Redirect Error
**Solution**: 
- Verify redirect URLs in OAuth provider settings match `.env` configuration
- Check `APP_URL` in `.env` is correct
- Clear application cache: `php artisan config:clear`

### Issue: Video Upload Fails
**Solution**:
- Check PHP `upload_max_filesize` and `post_max_size` settings
- Verify file is under 100MB
- Check file format (MP4, WebM, OGG)
- Ensure storage link is created: `php artisan storage:link`

### Issue: 3D Globe Not Loading
**Solution**:
- Check browser console for errors
- Verify `react-globe.gl` is installed: `npm install`
- Rebuild assets: `npm run build`
- Check if WebGL is enabled in browser

### Issue: Role Selection Not Appearing
**Solution**:
- Verify user doesn't have `role_selected = true` in database
- Check middleware is applied correctly
- Verify role seeder ran: `php artisan db:seed --class=RoleSeeder`

### Issue: Statistics Not Displaying
**Solution**:
- Check database has data
- Verify cache is working: `php artisan cache:clear`
- Check if models exist and have data
- Verify statistics calculation in `routes/web.php`

---

## Performance Testing

### File Upload Performance
- Test logo upload (should be fast, < 5MB)
- Test video upload (may take time for large files)
- Monitor upload progress
- Check file storage location

### 3D Globe Performance
- Test with many agents (100+)
- Check frame rate
- Monitor memory usage
- Test on different devices

### Page Load Performance
- Test main page load time
- Check dashboard load time
- Verify contacts page performance
- Monitor API response times

---

## Browser Compatibility

### Tested Browsers
- ✅ Chrome/Edge (Latest)
- ✅ Firefox (Latest)
- ✅ Safari (Latest)
- ⚠️ Internet Explorer (Not supported)

### Mobile Testing
- Test on iOS Safari
- Test on Android Chrome
- Verify responsive design
- Check touch interactions

---

## Security Testing

### OAuth Security
- Verify tokens are stored securely
- Check redirect URLs are validated
- Test CSRF protection
- Verify state parameter in OAuth flow

### File Upload Security
- Test file type validation
- Test file size limits
- Verify malicious file detection
- Check file storage permissions

### Authorization Testing
- Verify users can only edit their own profiles
- Test role-based access control
- Check middleware protection
- Verify API endpoint security

---

## Next Steps After Testing

1. **Fix Any Issues Found**
   - Document bugs
   - Create fixes
   - Re-test

2. **Performance Optimization**
   - Optimize database queries
   - Add caching where needed
   - Optimize file uploads
   - Improve 3D globe performance

3. **User Acceptance Testing**
   - Get feedback from users
   - Make UI/UX improvements
   - Add missing features

4. **Deployment Preparation**
   - Set up production environment
   - Configure production OAuth apps
   - Set up file storage (S3, etc.)
   - Configure CDN for assets

---

## Support

For issues or questions:
1. Check this guide first
2. Review implementation documentation
3. Check Laravel logs: `storage/logs/laravel.log`
4. Check browser console for frontend errors
5. Review database for data issues

