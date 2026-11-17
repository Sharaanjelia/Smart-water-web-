<?php

namespace App\Providers;

use App\Models\ChallengeParticipant;
use App\Models\ChallengeSubmission;
use App\Models\Complaint;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\UserLessonProgress;
use App\Models\UserQuizResult;
use App\Policies\AcademyProgressPolicy;
use App\Policies\ChallengeParticipantPolicy;
use App\Policies\ChallengeSubmissionPolicy;
use App\Policies\ComplaintPolicy;
use App\Policies\CustomerProfilePolicy;
use App\Policies\MeterReadingPolicy;
use App\Policies\NotificationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Complaint::class, ComplaintPolicy::class);
        Gate::policy(MeterReading::class, MeterReadingPolicy::class);
        Gate::policy(CustomerProfile::class, CustomerProfilePolicy::class);
        Gate::policy(Notification::class, NotificationPolicy::class);
        Gate::policy(UserLessonProgress::class, AcademyProgressPolicy::class);
        Gate::policy(UserQuizResult::class, AcademyProgressPolicy::class);
        Gate::policy(ChallengeParticipant::class, ChallengeParticipantPolicy::class);
        Gate::policy(ChallengeSubmission::class, ChallengeSubmissionPolicy::class);
    }
}
