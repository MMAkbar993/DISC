# DISC Leadership Assessment Tool

A comprehensive DISC personality assessment tool designed for leadership development and team analysis.

## Features

- ✅ **Fixed Validation**: Users cannot select MOST and LEAST from the same option
- ✅ **Volume Licensing**: Clear pricing tiers for individual, team, and enterprise usage
- ✅ **Admin Dashboard**: Complete administrative control panel for managing assessments
- ✅ **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices
- ✅ **Professional UI**: Modern, clean interface with customizable branding

## File Structure

```
leadership/
├── index.html              # Main assessment interface
├── admin.html              # Administrative dashboard
├── contact.html            # Enterprise contact form
├── css/
│   ├── style.css           # Main application styles
│   └── admin.css           # Admin panel styles
├── js/
│   ├── script.js           # Assessment functionality
│   └── admin.js            # Admin panel functionality
├── images/                 # Image assets directory
├── .htaccess              # Apache server configuration
├── README.md              # This file
└── DEPLOYMENT_CHECKLIST.md # Deployment verification checklist
```

## Deployment Instructions

### 1. Upload to Your Domain

Upload all files to your domain's `leadership` folder:

```
yourdomain.com/leadership/
├── index.html
├── admin.html
├── css/
├── js/
└── images/
```

### 2. Access URLs

- **Main Assessment**: `https://yourdomain.com/leadership/`
- **Admin Dashboard**: `https://yourdomain.com/leadership/admin.html`
- **Enterprise Contact**: `https://yourdomain.com/leadership/contact.html`

### 3. Admin Access

Default admin credentials:
- **Username**: `admin`
- **Password**: `disc2024admin`

⚠️ **Important**: Change these credentials immediately after deployment for security.

### 4. Configuration

The application uses localStorage for data persistence. For production use, consider implementing:

- Database backend for user management
- Server-side authentication
- API endpoints for assessment submission
- PDF generation service
- Email notifications

## Key Features Implemented

### 1. Validation Fix
- Users cannot select the same option for both MOST and LEAST
- Clear error messages with visual feedback
- Automatic error clearing after 5 seconds

### 2. Volume Licensing
- Individual assessment tier
- Team assessment tier (2-9 users)
- Enterprise tier (10+ users) with contact administrator option
- Professional pricing display

### 3. Admin Dashboard
- Access code generation (180 codes)
- Corporate branding customization
- PDF report settings
- Participant management
- Team report generation

## Technical Requirements

- Modern web browser (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- No server-side requirements (client-side only)

## Customization

### Branding
Use the admin panel to customize:
- Company logo and name
- Color scheme
- Font family
- Background images
- Report settings

### Assessment Questions
Modify the `questions` array in `js/script.js` to customize assessment content.

## Security Considerations

1. Change default admin credentials
2. Implement server-side validation for production
3. Add HTTPS for secure data transmission
4. Consider rate limiting for API endpoints
5. Implement proper user authentication

## Support

For technical support or customization requests, contact the development team.

## License

This software is provided for use with proper licensing agreements for enterprise deployments.
