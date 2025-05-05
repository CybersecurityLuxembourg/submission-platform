# Project Name

## Overview


The NC3 Submission Platform is a sophisticated form management system designed for secure submission of different forms. It combines flexible access controls and collaborative features.


## Key Features

### Dynamic Form Builder
- No-code form creation interface
- Custom workflow support [WIP]
- File upload capabilities
- Drafting before submitting

## Requirements

- PHP 8.2+
- Laravel Framework
- API Documentation via Scramble
- Frontend with Livewire and Filament

## Installation

1. Clone the repository:
```bash
git clone [repository-url]
```

2. Install dependencies:
```bash
composer install
```

3. Configure your environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Update your `.env` file with appropriate settings

## API Documentation

API documentation is automatically generated using Scramble and can be accessed at `/docs/api` when running the application. The OpenAPI specification is exported to `api.json`.

## Security

For security-related matters, please contact:
- Security issues: abuse@lhc.lu
- Data protection: privacy@lhc.lu

or via GitHub Security Advisory for critical vulnerabilities
https://github.com/CybersecurityLuxembourg/submission-platform/security/advisories

## License

This project is licensed under the GNU Affero General Public License v3.0. See the [LICENSE](LICENSE) file for details.


## Support

For general inquiries:
- Email: info@lhc.lu
- Address: Luxembourg House of Cybersecurity, 122, Rue Adolphe Fischer, L-1521 Luxembourg


## Roadmap
- CLAM AV integration for file uploads
- Access links for specific emails which need to be confirmed via an unique code which is sent via mail
- Custom Workflow definition after a submission
    - Who shall do what
    - Who shall be notified
    - Shall the user resubmit
    - etc
- API extension
- Encrypted file storage
