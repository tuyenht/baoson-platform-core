# PHP Extensions Setup

## Required Extensions

Baoson Platform Core requires the following PHP extensions:

- `pdo_sqlite` - For SQLite database support
- `fileinfo` - For file type detection (required by Laravel)
- `mbstring` - For string manipulation
- `openssl` - For encryption
- `tokenizer` - For code parsing
- `xml` - For XML processing

## Enabling Extensions on Windows

### Current Issue: fileinfo Extension

The `fileinfo` extension is currently disabled. To enable it:

1. **Locate php.ini file:**
   ```powershell
   php --ini
   ```
   Output shows: `C:\php-8.3.12\php.ini`

2. **Edit php.ini:**
   - Open `C:\php-8.3.12\php.ini` in a text editor (as Administrator)
   - Find the line: `;extension=fileinfo`
   - Remove the semicolon to uncomment: `extension=fileinfo`
   - Save the file

3. **Verify:**
   ```powershell
   php -r "echo extension_loaded('fileinfo') ? 'fileinfo: ENABLED' : 'fileinfo: DISABLED';"
   ```

4. **Restart any running PHP processes** (if any)

### Temporary Workaround

If you cannot enable the extension immediately, use Composer with the ignore flag:

```bash
composer install --ignore-platform-req=ext-fileinfo
composer update --ignore-platform-req=ext-fileinfo
```

**Note:** This is a temporary workaround. The extension should be enabled for production use.

## Checking All Extensions

To check which extensions are loaded:

```powershell
php -m
```

To check a specific extension:

```powershell
php -r "echo extension_loaded('fileinfo') ? 'ENABLED' : 'DISABLED';"
```

## Common Extensions to Enable

If you encounter other missing extensions, uncomment them in `php.ini`:

```ini
extension=pdo_sqlite
extension=fileinfo
extension=mbstring
extension=openssl
extension=tokenizer
extension=xml
extension=curl
extension=gd
extension=intl
```

## Troubleshooting

### Extension Not Loading

1. Ensure the extension DLL file exists in the PHP `ext/` directory
2. Check that the path in `php.ini` is correct:
   ```ini
   extension_dir = "ext"
   ```
3. Verify PHP version matches the extension DLL version
4. Restart your terminal/command prompt after changes

### Permission Issues

- Edit `php.ini` as Administrator
- Ensure PHP has read access to the `ext/` directory


