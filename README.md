<p align="center">
  <a href="https://packagist.org/packages/wpmvc/helpers"><img src="https://img.shields.io/packagist/dt/wpmvc/helpers" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/wpmvc/helpers"><img src="https://img.shields.io/packagist/v/wpmvc/helpers" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/wpmvc/helpers"><img src="https://img.shields.io/packagist/l/wpmvc/helpers" alt="License"></a>
</p>

# WpMVC Helpers

**WpMVC Helpers** provides a collection of static utility methods to simplify common WordPress tasks such as file uploads, plugin metadata, array operations, request handling, and data sanitation.

These methods are framework-agnostic and can be used across any part of your WpMVC-based plugin.

---

### 📦 Installation

To install the `Helpers` package:

```bash
composer require wpmvc/helpers
```

### 📂 Class Location

```php
namespace WpMVC\Helpers;
class Helpers
```

---

### 🔧 Available Static Methods

---

#### 🔹 `get_plugin_version(string $plugin_slug): ?string`

Retrieves the version of a plugin based on its slug.

```php
$version = Helpers::get_plugin_version('my-plugin');
```

Returns `null` if the plugin file doesn't exist or version is not found.

---

#### 🔹 `upload_file(array $file, bool $create_attachment = true): array|int`

Handles file upload and optionally creates a WordPress media attachment.

```php
$attachment_id = Helpers::upload_file($_FILES['my_file']);
```

* Returns attachment ID if `$create_attachment` is `true`
* Returns file metadata array if `false`
* Throws `Exception` on upload failure

---

#### 🔹 `delete_attachments_by_ids(int|int[] $attachment_ids): int[]`

Deletes one or multiple media attachments by ID(s).

```php
Helpers::delete_attachments_by_ids([10, 12]);
```

Returns an array of successfully deleted attachment IDs.

---

#### 🔹 `request(): WP_REST_Request`

Creates and populates a `WP_REST_Request` instance using global request data.

```php
$request = Helpers::request();
```

Populates:

* Query params from `$_GET`
* Body from `$_POST`
* Files from `$_FILES`
* Headers from `$_SERVER`
* Raw body data

---

#### 🔹 `maybe_json_decode(mixed $value): mixed`

Attempts to decode a JSON string if valid; returns original value if not.

```php
$data = Helpers::maybe_json_decode('{"foo": "bar"}');
```

---

#### 🔹 `is_one_level_array(array $array): bool`

Checks whether the array contains only scalar values (no nested arrays).

```php
Helpers::is_one_level_array(['a', 'b']); // true
Helpers::is_one_level_array(['a' => ['nested']]); // false
```

---

#### 🔹 `array_merge_deep(array $array1, array $array2): array`

Recursively merges two arrays.

```php
$merged = Helpers::array_merge_deep($a, $b);
```

Preserves nested structures instead of overwriting them.

---

#### 🔹 `remove_null_values(array $array): array`

Removes all `null` values from an array.

```php
$data = Helpers::remove_null_values([
    'a' => 'value',
    'b' => null,
]); // ['a' => 'value']
```

---

#### 🔹 `get_user_ip_address(): ?string`

Detects the user's IP address, accounting for proxies and headers.

```php
$ip = Helpers::get_user_ip_address();
```

Checks in order:

1. `HTTP_CLIENT_IP`
2. `HTTP_X_FORWARDED_FOR`
3. `REMOTE_ADDR`

Returns `null` if none are valid.

---

### ✅ Summary Table

| Method                        | Purpose                                                 |
| ----------------------------- | ------------------------------------------------------- |
| `get_plugin_version()`        | Reads plugin version from header                        |
| `upload_file()`               | Uploads file + optionally creates media attachment      |
| `delete_attachments_by_ids()` | Deletes one or more attachments by ID                   |
| `request()`                   | Creates a populated `WP_REST_Request` from global input |
| `maybe_json_decode()`         | Safely attempts to decode JSON                          |
| `is_one_level_array()`        | Checks for nested arrays                                |
| `array_merge_deep()`          | Deep merges two arrays recursively                      |
| `remove_null_values()`        | Removes all `null` values from an array                 |
| `get_user_ip_address()`       | Gets real client IP considering proxies                 |
