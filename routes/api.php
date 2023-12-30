<?php

use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\GroupWorkoutController;
use App\Http\Controllers\Admin\GymController;
use App\Http\Controllers\Admin\LimitedPriceListController;
use App\Http\Controllers\Admin\LimitedSubscriptionController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SignUpGroupWorkoutController;
use App\Http\Controllers\Admin\SignUpPersonalWorkoutController;
use App\Http\Controllers\Admin\UnlimitedPriceListController;
use App\Http\Controllers\Admin\UnlimitedSubscriptionController;
use App\Http\Controllers\Admin\WorkoutTypeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

//Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function (){
    Route::get('user', [AuthController::class, 'user']);
    Route::post('logout', [AuthController::class, 'logout']);

    // получить все уведомления авторизированного пользователя
    Route::get('get-all-notifications', [UserController::class, 'getAllNotifications']);
    // получить непрочитанные уведомления авторизированного пользователя
    Route::get('get-unread-notification', [UserController::class, 'getUnreadNotifications']);

    // отметить сообщение как прочитанное (доступ по id)
    Route::post('mark-as-read-by-id', [UserController::class, 'markAsReadById']);
    // отметить все уведомления как прочитанные
    Route::get('all-mark-as-read', [UserController::class, 'allMarkAsRead']);

    // получить количество непрочитанных уведомлений
    Route::get('get-count-unread-notification', [UserController::class, 'getUnreadNotificationsCount']);

    // Сторона Администратора:
    Route::middleware(['restrictRole:admin'])->group(function (){

        Route::get('test', [SignUpPersonalWorkoutController::class, 'signUpPersonalWorkoutsSeeder']);

        // получить все записи (вывод всех тренеров)
        Route::get('coaches/get-all', [CoachController::class, 'coachesAll']);
        // получить все записи (вывод всех тренеров) постранично
        Route::get('coaches/all', [CoachController::class, 'coaches']);
        // сохранить/редактировать тренера в бд
        Route::post('coaches/add', [CoachController::class, 'decorCoach']);
        Route::post('coaches/edit', [CoachController::class, 'decorCoach']);
        // увольнение(удаление) тренера
        Route::post('coaches/delete', [CoachController::class, 'deleteCoach']);

        //сколько тренировок нужно выставить тренеру в расписании(за неделю) и сколько он выставил
        Route::get('coaches/required-amount-workouts/{id}', [CoachController::class, 'requiredAmountWorkouts']);

        // получить все записи (вывод всех клиентов)
        Route::get('customers/get-all', [CustomerController::class, 'customersAll']);
        // получить все записи (вывод всех клиентов) постранично
        Route::get('customers/all', [CustomerController::class, 'customers']);

        // проверка: данных для добавления клиента
        Route::post('customers/validate', [CustomerController::class, 'checkDataForCustomerAdd']);

        // сохранить/редактировать клиента в бд
        Route::post('customers/add', [CustomerController::class, 'addCustomer']);
        Route::post('customers/edit', [CustomerController::class, 'editCustomer']);
        // поиск клиента по серии-номеру паспорта
        Route::post('customers/select-customers-by-passport', [CustomerController::class, 'getCustomersByPassport']);


        // вывод всех спортзалов
        Route::get('gyms/get-all', [GymController::class, 'getAllGyms']);

        Route::get('workout-types/get-all', [WorkoutTypeController::class, 'getAllWorkoutTypes']);

        // получить все записи (вывод всех групповых тренировок)
        Route::get('group-workouts/get-all', [GroupWorkoutController::class, 'getGroupWorkouts']);
        // получить все записи (вывод всех групповых тренировок) постранично
        Route::get('group-workouts/all', [GroupWorkoutController::class, 'groupWorkouts']);
        //получить всю информацию о групповой тренировки по id
        Route::get('group-workouts/select-by-id/{id}', [GroupWorkoutController::class, 'groupWorkoutById']);
        //редактирование тренировки - возможна только отмена
        Route::post('group-workouts/group-workout-edit', [GroupWorkoutController::class, 'groupWorkoutEdit']);
        // получить все тренировки пройденные через фильтр
        Route::get('group-workouts/filtered/', [GroupWorkoutController::class, 'groupWorkoutsFiltered']);
        // показать тренировки по расписанию
        Route::get('group-workouts/select-workouts-by-schedule/', [GroupWorkoutController::class, 'selectWorkoutsBySchedule']);

        // получить все записи (вывести прайс-лист на тренировки с тренерами) постранично
        Route::get('limited-price-lists/all', [LimitedPriceListController::class, 'limitedPriceLists']);
        // получить все записи (вывести прайс-лист на тренировки с тренерами)
        Route::get('limited-price-lists/get-all', [LimitedPriceListController::class, 'getLimitedPriceLists']);

        // получить все записи (вывести все подписки на тренировки с тренерами)
        Route::get('limited-subscriptions/get-all', [LimitedSubscriptionController::class, 'getLimitedSubscriptions']);
        // получить все записи (вывести все подписки на тренировки с тренерами) постранично
        Route::get('limited-subscriptions/all', [LimitedSubscriptionController::class, 'limitedSubscriptions']);
        //добавить подписку на групповые тренировки
        Route::post('limited-subscriptions/add', [LimitedSubscriptionController::class, 'addLimitedSubscription']);

        // добавить/редактировать тренировку в расписание
        Route::post('schedules/add', [ScheduleController::class, 'decorSchedule']);
        Route::post('schedules/edit', [ScheduleController::class, 'decorSchedule']);
        // удалить запись расписания
        Route::post('schedules/delete', [ScheduleController::class, 'deleteSchedule']);

        // получить все записи на групповые тренировки
        Route::get('sign-up-group-workouts/all', [SignUpGroupWorkoutController::class, 'signUpGroupWorkouts']);
        //получить всю информацию о групповой тренировки по id
        Route::get('sign-up-group-workouts/select-by-workout-id/{id}', [SignUpGroupWorkoutController::class, 'selectSignUpGroupWorkoutsByWorkoutId']);

        // получить все записи на персональные тренировки
        Route::get('sign-up-personal-workouts/all', [SignUpPersonalWorkoutController::class, 'signUpPersonalWorkouts']);
        //получить все тренировки пройденные через фильтр
        Route::get('sign-up-personal-workouts/filtered/', [SignUpPersonalWorkoutController::class, 'signUpPersonalWorkoutsFiltered']);

        Route::get('sign-up-personal-workouts/get-sign-up-personal-workouts-by-coach/{id}/{page}', [SignUpPersonalWorkoutController::class, 'getSignUpPersonalWorkoutsByCoach']);

        // получить все записи (вывести прайс-лист на безлимит абонементы)
        Route::get('unlimited-price-lists/all', [UnlimitedPriceListController::class, 'unlimitedPriceLists']);

        // получить все записи (вывести все подписки на безлимит абонемент)
        Route::get('unlimited-subscriptions/get-all', [UnlimitedSubscriptionController::class, 'getAllUnlimitedSubscriptions']);
        // получить все записи (вывести все подписки на безлимит абонемент) постранично
        Route::get('unlimited-subscriptions/all', [UnlimitedSubscriptionController::class, 'unlimitedSubscriptions']);
        // Сторона Администратора: безлимит абонементы данного клиента.
        Route::post('unlimited-subscriptions/select-unlimited-subscriptions-by-customer', [UnlimitedSubscriptionController::class, 'selectUnlimitedSubscriptionsByCustomer']);
        // добавить абонемент
        Route::post('unlimited-subscriptions/add', [UnlimitedSubscriptionController::class, 'addUnlimitedSubscription']);

        // Сторона Администратора: купленные тренировки данного клиента.
        Route::post('limited-subscriptions/select-limited-subscriptions-by-customer', [LimitedSubscriptionController::class, 'selectLimitedSubscriptionsByCustomer']);

    });

    // Сторона Администратора и Тренера
    Route::middleware(["restrictRole:coach,admin"])->group(function (){
        // вывести расписание групповых тренировок
        Route::get('schedules/all', [ScheduleController::class, 'schedulesGetAll']);
    });

    // Сторона Клиента и Тренера
    Route::middleware(["restrictRole:coach,customer"])->group(function (){

        // получить изображение
        Route::get('/get-image', [UserController::class, 'getImage']);
        //загрузить файл
        Route::post('/upload', [UserController::class, 'upload']);
    });

    // Сторона Клиента
    Route::middleware(['restrictRole:customer'])->prefix('customer')->group(function (){

        // узнать количество тренировок в абонементе
        Route::get('/about-amount-workouts', [\App\Http\Controllers\Customer\CustomerController::class, 'aboutAmountWorkouts']);

        //получает информацию о текущем абонементе (безлимит)
        Route::get('/about-subscription', [\App\Http\Controllers\Customer\CustomerController::class, 'aboutSubscription']);

        //получает информацию о текущем абонементе (тренировки с тренером)
        Route::get('/about-subscription-with-coach', [\App\Http\Controllers\Customer\CustomerController::class, 'aboutSubscriptionWithCoach']);

        // получить все доступные тренировки для записи клиента
        Route::get('/get-available-workouts', [\App\Http\Controllers\Customer\CustomerController::class, 'getAvailableWorkouts']);

        // получить все актуальные записи клиента (на которые клиент может прийти)
        Route::get('/current-sign-up', [\App\Http\Controllers\Customer\CustomerController::class, 'currentSignUp']);

        // запись клиента на тренировки
        Route::post('/sign-up', [\App\Http\Controllers\Customer\CustomerController::class, 'signUp']);

        //отмена записи на групповую тренировку
        Route::post('/delete-sign-up', [\App\Http\Controllers\Customer\CustomerController::class, 'deleteSignUpGroupWorkout']);

        // получить все доступные персональные тренировки для записи клиента
        Route::get('/get-personal-available-workouts', [\App\Http\Controllers\Customer\CustomerController::class, 'getPersonalWorkouts']);

        // записаться на персональную тренировку
        Route::post('/sign-up-personal-workout', [\App\Http\Controllers\Customer\CustomerController::class, 'singUpForPersonalWorkout']);

        // получить свои актуальные записи на персональные тренировки
        Route::get('/get-sign-up-personal-workouts', [\App\Http\Controllers\Customer\CustomerController::class, 'getSingUpForPersonalWorkout']);

        //отмена записи на персональную тренировку
        Route::post('/delete-sign-up-personal-workouts', [\App\Http\Controllers\Customer\CustomerController::class, 'deleteSingUpForPersonalWorkout']);

    });

    // Сторона Тренера
    Route::middleware(['restrictRole:coach'])->prefix('coach')->group(function (){

        // может изменить цену на абонемент (упрощение модели)
        Route::post('/edit-limited-price', [\App\Http\Controllers\Coach\CoachController::class, 'editLimitedPrice']);

        // получить признак доступна ли продажа абонементов
        Route::get('/get-sale', [\App\Http\Controllers\Coach\CoachController::class, 'getSale']);

        // тренер может запретить продажу абонементов
        Route::get('/change-sale', [\App\Http\Controllers\Coach\CoachController::class, 'changeSale']);

        // получить тренера из авторизированного пользователя
        Route::get('/get-coach', [\App\Http\Controllers\Coach\CoachController::class, 'getCoachJSON']);

        // получить дату последнего изменения расписания
        Route::get('/get-date-of-change', [\App\Http\Controllers\Coach\CoachController::class, 'getDateOfChange']);

        // получить расписание авторизированного тренера
        Route::get('/get-schedule', [\App\Http\Controllers\Coach\CoachController::class, 'getSchedule']);

        // получить записи на персональные тренировки для авторизированного тренера
        Route::get('/get-sign-up-personal-workouts-by-auth-coach/{page}', [\App\Http\Controllers\Coach\CoachController::class, 'getSignUpPersonalWorkoutsByAuthCoach']);

        // сколько тренировок нужно выставить авторизированному тренеру в расписании(за неделю) и сколько он выставил
        Route::get('/get-required-amount-workouts', [\App\Http\Controllers\Coach\CoachController::class, 'getRequiredAmountWorkouts']);

        // добавление тренировки в расписание
        Route::post('/add-workout', [\App\Http\Controllers\Coach\CoachController::class, 'addWorkout']);

        // получить расписание для редактирования
        Route::get('/get-schedule-for-edit', [\App\Http\Controllers\Coach\CoachController::class, 'getScheduleForEdit']);

        // редактирование расписания
        Route::post('/schedule-edit', [\App\Http\Controllers\Coach\CoachController::class, 'editSchedule']);

    });

});





