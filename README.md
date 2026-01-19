<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Open SensorThings

## Introduction

Open SensorThings is a system that implements the OGC SensorThings API standard, supporting the management, collection, and exploitation of sensor data for IoT, Smart City, and digital transformation applications.

The system is designed with a modern architecture, easy to extend, and suitable for both research and real-world deployment environments.

## Version

- Current Sensing version: **2.2**
- Current Tasking version: **1.2**

## Key Features

- Compliant with OGC SensorThings API standard
- Management of Things, Locations, Datastreams, and Observations
- Real-time data support
- Easy integration with external systems and services

## Installation

### Requirements

- PHP 8.0.x
- Composer
- MySQL 8.0.x

### Setup Steps

**Step 1: Clone the project**

```bash
git clone <repository-url>
```

**Step 2: Create environment configuration**

Create a `.env` file by copying the sample configuration file:

```bash
cp .env.example .env
```

Then update the database configuration in the `.env` file to match your local MySQL setup.

**Step 3: Create database schema**

Run the following command to generate the initial database structure:

```bash
php artisan migrate
```

**Step 4: Run the application**

Start the Laravel development server:

```bash
php artisan serve
```

### Notes

- When the web interface prompts you to generate an application key, please click **Generate New Key** to continue.

## Contributors

- Project Director: Truong Xuan Viet
- Platform Developer: Phung Gia Kien, Dam Quoc Thinh, Truong Xuan Viet, Nguyen Phan Thanh Tuong, Pham Vo Cong Thien
- Operator Tester: Ly Duc Minh, Nguyen Dai Nghia
- Tester: Le Phan Thao Anh, Nguyen Phan Thanh Tuong
- Hardware developers: Ly Duc Minh, Nguyen Dai Nghia
- Used by Smart Garden Team: Huynh Quy Khang, Nguyen Dai Nghia, Duong Nhat Duy, Tu Huu Duc, Duong Nhut Truong

## License

© 2022 Open SensorThings. Developed and maintained by CUSC.
