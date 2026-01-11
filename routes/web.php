<?php

use App\Http\Controllers\AdminBoardingSchoolController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BoardingSchoolController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/berita', [LandingController::class, 'news'])->name('news');
Route::get('/berita/{slug}', [LandingController::class, 'newsDetail'])->name('news.detail');

// Student Registration
Route::get('/register-santri', [\App\Http\Controllers\StudentRegistrationController::class, 'create'])->name('register');
Route::post('/register-santri', [\App\Http\Controllers\StudentRegistrationController::class, 'store'])->name('register.store');
Route::get('/register-santri/success', [\App\Http\Controllers\StudentRegistrationController::class, 'success'])->name('register.success');
Route::get('/register-santri/download/statement', [\App\Http\Controllers\StudentRegistrationController::class, 'downloadStatement'])->name('register.download.statement');
Route::get('/register-santri/download/form', [\App\Http\Controllers\StudentRegistrationController::class, 'downloadForm'])->name('register.download.form');

// Public Region Routes
Route::prefix('region')->name('region.')->group(function () {
    Route::get('provinces', [\App\Http\Controllers\RegionController::class, 'provinces'])->name('provinces');
    Route::get('{province}/regencies', [\App\Http\Controllers\RegionController::class, 'regencies'])->name('regencies');
    Route::get('{regency}/districts', [\App\Http\Controllers\RegionController::class, 'districts'])->name('districts');
    Route::get('{district}/villages', [\App\Http\Controllers\RegionController::class, 'villages'])->name('villages');
});

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Google OAuth
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Google Account Linking
    Route::get('/auth/google/link', [GoogleController::class, 'linkRedirect'])->name('google.link');
    Route::get('/auth/google/link/callback', [GoogleController::class, 'linkCallback']);

    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Dashboard Home
        Route::get('/', function () {
            return Inertia::render('Dashboard/Index', ['posts' => []]);
        })->name('index');

        // Profile Routes (All Authenticated Users)
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'show'])->name('show');
            Route::put('/', [ProfileController::class, 'update'])->name('update');
            Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password');
            Route::post('/photo', [ProfileController::class, 'updatePhoto'])->name('photo');
            Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('photo.delete');
            Route::delete('/google', [ProfileController::class, 'unlinkGoogle'])->name('google.unlink');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin Pondok Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin-pondok')->group(function () {
            // Boarding Schools - Admin Pondok can only edit
            Route::get('boarding-schools', [BoardingSchoolController::class, 'index'])->name('boarding-schools.index');
            Route::get('boarding-schools/{boarding_school}', [BoardingSchoolController::class, 'show'])->name('boarding-schools.show')->middleware('scope.boarding_school');
            Route::get('boarding-schools/{boarding_school}/edit', [BoardingSchoolController::class, 'edit'])->name('boarding-schools.edit')->middleware('scope.boarding_school');
            Route::put('boarding-schools/{boarding_school}', [BoardingSchoolController::class, 'update'])->name('boarding-schools.update')->middleware('scope.boarding_school');

            // Students - Admin Pondok only
            Route::get('students/export/excel', [StudentController::class, 'export'])->name('students.export');
            Route::get('students/template/download', [StudentController::class, 'downloadTemplate'])->name('students.template');
            Route::post('students/import/excel', [StudentController::class, 'import'])->name('students.import');
            Route::get('students', [StudentController::class, 'index'])->name('students.index');
            Route::get('students/create', [StudentController::class, 'create'])->name('students.create');
            Route::post('students', [StudentController::class, 'store'])->name('students.store');
            Route::get('students/{student}', [StudentController::class, 'show'])->name('students.show')->middleware('scope.student');
            Route::get('students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit')->middleware('scope.student');
            Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update')->middleware('scope.student');
            Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy')->middleware('scope.student');
            
            // Student Registration (PSB Management)
            Route::get('student-registrations', [\App\Http\Controllers\BoardingSchoolStudentRegistrationController::class, 'index'])->name('student-registrations.index');
            Route::get('student-registrations/{registration}', [\App\Http\Controllers\BoardingSchoolStudentRegistrationController::class, 'show'])->name('student-registrations.show');
            Route::post('student-registrations/{registration}/accept', [\App\Http\Controllers\BoardingSchoolStudentRegistrationController::class, 'accept'])->name('student-registrations.accept');
            Route::get('student-registrations/{registration}/pdf', [\App\Http\Controllers\BoardingSchoolStudentRegistrationController::class, 'downloadPdf'])->name('student-registrations.pdf');
            
            // Student Details - Diseases
            Route::post('students/{student}/diseases', [\App\Http\Controllers\StudentDetailController::class, 'storeDisease'])->name('students.diseases.store')->middleware('scope.student');
            Route::delete('students/{student}/diseases/{disease}', [\App\Http\Controllers\StudentDetailController::class, 'destroyDisease'])->name('students.diseases.destroy')->middleware('scope.student');
            
            // Student Details - Permissions (Izin)
            Route::post('students/{student}/permissions', [\App\Http\Controllers\StudentDetailController::class, 'storePermission'])->name('students.permissions.store')->middleware('scope.student');
            Route::put('students/{student}/permissions/{permission}', [\App\Http\Controllers\StudentDetailController::class, 'updatePermission'])->name('students.permissions.update')->middleware('scope.student');
            Route::put('students/{student}/permissions/{permission}/return', [\App\Http\Controllers\StudentDetailController::class, 'returnPermission'])->name('students.permissions.return')->middleware('scope.student');
            Route::get('students/{student}/permissions/{permission}/print', [\App\Http\Controllers\StudentDetailController::class, 'printPermission'])->name('students.permissions.print')->middleware('scope.student');
            Route::delete('students/{student}/permissions/{permission}', [\App\Http\Controllers\StudentDetailController::class, 'destroyPermission'])->name('students.permissions.destroy')->middleware('scope.student');
            
            // Student Details - Violations (Pelanggaran)
            Route::post('students/{student}/violations', [\App\Http\Controllers\StudentDetailController::class, 'storeViolation'])->name('students.violations.store')->middleware('scope.student');
            Route::delete('students/{student}/violations/{violation}', [\App\Http\Controllers\StudentDetailController::class, 'destroyViolation'])->name('students.violations.destroy')->middleware('scope.student');

            // Student Details - Memorizes (Hafalan)
            Route::post('students/{student}/memorizes', [\App\Http\Controllers\StudentDetailController::class, 'storeMemorize'])->name('students.memorizes.store')->middleware('scope.student');
            Route::delete('students/{student}/memorizes/{memorize}', [\App\Http\Controllers\StudentDetailController::class, 'destroyMemorize'])->name('students.memorizes.destroy')->middleware('scope.student');

            // Student RFID Assignment
            Route::get('student-rfid', [\App\Http\Controllers\StudentRfidController::class, 'index'])->name('student-rfid.index');
            Route::put('student-rfid/{student}', [\App\Http\Controllers\StudentRfidController::class, 'update'])->name('student-rfid.update')->middleware('scope.student');

            // Student Withdraw Limit (Finance)
            Route::get('finance/student-withdraw-limit', [\App\Http\Controllers\StudentWithdrawLimitController::class, 'index'])->name('finance.student-withdraw-limit.index');
            Route::put('finance/student-withdraw-limit', [\App\Http\Controllers\StudentWithdrawLimitController::class, 'update'])->name('finance.student-withdraw-limit.update');

            // Student Balance Management
            Route::get('finance/student-balance', [\App\Http\Controllers\StudentBalanceController::class, 'index'])->name('finance.student-balance.index');
            Route::post('finance/student-balance/{student}/topup', [\App\Http\Controllers\StudentBalanceController::class, 'topup'])->name('finance.student-balance.topup')->middleware('scope.student');
            Route::post('finance/student-balance/{student}/withdraw', [\App\Http\Controllers\StudentBalanceController::class, 'withdraw'])->name('finance.student-balance.withdraw')->middleware('scope.student');
            Route::put('finance/student-balance/{student}/pin', [\App\Http\Controllers\StudentBalanceController::class, 'updatePin'])->name('finance.student-balance.updatePin')->middleware('scope.student');

            // Student Withdraw via RFID
            Route::post('finance/student-withdraw/show-by-rfid', [\App\Http\Controllers\StudentWithdrawController::class, 'showByRfid'])->name('finance.student-withdraw.show');
            Route::post('finance/student-withdraw/process', [\App\Http\Controllers\StudentWithdrawController::class, 'processWithdraw'])->name('finance.student-withdraw.process');

            // Student Withdraw History
            Route::get('finance/student-balance/history', [\App\Http\Controllers\StudentWithdrawHistoryController::class, 'index'])->name('finance.student-balance.history');
            Route::get('finance/student-balance/history/export', [\App\Http\Controllers\StudentWithdrawHistoryController::class, 'export'])->name('finance.student-balance.history.export');

            // Student Savings Management
            Route::get('finance/student-savings', [\App\Http\Controllers\StudentSavingsController::class, 'index'])->name('finance.student-savings.index');
            Route::post('finance/student-savings/{student}/deposit', [\App\Http\Controllers\StudentSavingsController::class, 'deposit'])->name('finance.student-savings.deposit')->middleware('scope.student');
            Route::post('finance/student-savings/{student}/withdraw', [\App\Http\Controllers\StudentSavingsController::class, 'withdraw'])->name('finance.student-savings.withdraw')->middleware('scope.student');
            Route::get('finance/student-savings/{student}/history', [\App\Http\Controllers\StudentSavingsController::class, 'history'])->name('finance.student-savings.history')->middleware('scope.student');
            Route::get('finance/student-savings/{student}/export', [\App\Http\Controllers\StudentSavingsController::class, 'export'])->name('finance.student-savings.export')->middleware('scope.student');

            // Student Invoices (Tagihan)
            Route::get('finance/student-invoices/search-students', [\App\Http\Controllers\StudentInvoiceController::class, 'searchStudents'])->name('finance.student-invoices.search-students');
            Route::post('finance/student-invoices/{studentInvoice}/pay/{student}', [\App\Http\Controllers\StudentInvoiceController::class, 'payOffline'])->name('finance.student-invoices.pay-offline');
            Route::resource('finance/student-invoices', \App\Http\Controllers\StudentInvoiceController::class)->names('finance.student-invoices');

            // Teachers - Admin Pondok only
            Route::get('teachers', [\App\Http\Controllers\TeacherController::class, 'index'])->name('teachers.index');
            Route::get('teachers/create', [\App\Http\Controllers\TeacherController::class, 'create'])->name('teachers.create');
            Route::post('teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
            Route::get('teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'show'])->name('teachers.show')->middleware('scope.teacher');
            Route::get('teachers/{teacher}/edit', [\App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit')->middleware('scope.teacher');
            Route::put('teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'update'])->name('teachers.update')->middleware('scope.teacher');
            Route::delete('teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'destroy'])->name('teachers.destroy')->middleware('scope.teacher');
            Route::get('teachers/export/excel', [\App\Http\Controllers\TeacherController::class, 'export'])->name('teachers.export');
            Route::get('teachers/template/download', [\App\Http\Controllers\TeacherController::class, 'downloadTemplate'])->name('teachers.template');
            Route::post('teachers/import/excel', [\App\Http\Controllers\TeacherController::class, 'import'])->name('teachers.import');
            
            // Letter Settings
            Route::get('settings/letter', [\App\Http\Controllers\LetterSettingController::class, 'edit'])->name('settings.letter.edit');
            Route::patch('settings/letter', [\App\Http\Controllers\LetterSettingController::class, 'update'])->name('settings.letter.update');
            
            // Classrooms
            Route::resource('classrooms', \App\Http\Controllers\ClassroomController::class);
            Route::resource('bed-rooms', \App\Http\Controllers\BedRoomController::class);
        });

        /*
        |--------------------------------------------------------------------------
        | Super Admin Only Routes
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:super-admin')->group(function () {
            // School Years - Super Admin only
            Route::resource('school-years', SchoolYearController::class)->except(['show', 'create', 'edit']);
            Route::post('school-years/{school_year}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');

            // Boarding Schools - Super Admin full CRUD
            Route::resource('boarding-schools', BoardingSchoolController::class)->except(['index', 'show', 'edit', 'update']);
            Route::get('boarding-schools/{boarding_school}/admins', [AdminBoardingSchoolController::class, 'index'])->name('boarding-schools.admins.index');
            Route::post('boarding-schools/{boarding_school}/admins', [AdminBoardingSchoolController::class, 'store'])->name('boarding-schools.admins.store');
            Route::delete('boarding-schools/{boarding_school}/admins/{admin}', [AdminBoardingSchoolController::class, 'destroy'])->name('boarding-schools.admins.destroy');

            // Master Data
            Route::resource('schools', SchoolController::class);

            Route::resource('positions', PositionController::class);

            // Users
            Route::get('users', function () {
                return Inertia::render('Dashboard/Users', [
                    'users' => \App\Models\User::with('roles')->latest()->paginate(10),
                ]);
            })->name('users');

            // CMS Management
            Route::prefix('cms')->name('cms.')->group(function () {
                // Posts
                Route::resource('posts', \App\Http\Controllers\CMS\PostController::class);

                // Programs
                Route::resource('programs', \App\Http\Controllers\CMS\ProgramController::class);

                // Galleries
                Route::resource('galleries', \App\Http\Controllers\CMS\GalleryController::class);

                // Testimonials
                Route::resource('testimonials', \App\Http\Controllers\CMS\TestimonialController::class);

                // FAQs
                Route::resource('faqs', \App\Http\Controllers\CMS\FaqController::class);

                // Web Settings
                Route::get('settings', [\App\Http\Controllers\CMS\SettingController::class, 'index'])->name('settings.index');
                Route::post('settings', [\App\Http\Controllers\CMS\SettingController::class, 'update'])->name('settings.update');
            });
        });
        
        // Region Routes moved to public
    });
});

