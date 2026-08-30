<?php

namespace App\Providers;

use App\Models\Accreditation;
use App\Models\AccreditationScoreSnapshot;
use App\Models\AccreditationSubmission;
use App\Models\AmiChecklistItem;
use App\Models\AmiCycle;
use App\Models\AmiFinding;
use App\Models\DocumentGenerationRequest;
use App\Models\RtmDecision;
use App\Models\RtmMeeting;
use App\Models\UserTenantScope;
use App\Observers\AmiCycleObserver;
use App\Observers\AmiFindingObserver;
use App\Observers\RtmDecisionObserver;
use App\Observers\RtmMeetingObserver;
use App\Observers\UserTenantScopeObserver;
use App\Policies\AccreditationPolicy;
use App\Policies\AccreditationScoreSnapshotPolicy;
use App\Policies\AccreditationSubmissionPolicy;
use App\Policies\AmiChecklistItemPolicy;
use App\Policies\AmiCyclePolicy;
use App\Policies\AmiFindingPolicy;
use App\Policies\DocumentGenerationRequestPolicy;
use App\Policies\RtmDecisionPolicy;
use App\Policies\RtmMeetingPolicy;
use App\Policies\UserTenantScopePolicy;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, static fn(): TenantContext => new TenantContext);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        UserTenantScope::observe(UserTenantScopeObserver::class);
        AmiCycle::observe(AmiCycleObserver::class);
        AmiFinding::observe(AmiFindingObserver::class);
        RtmMeeting::observe(RtmMeetingObserver::class);
        RtmDecision::observe(RtmDecisionObserver::class);

        Gate::policy(Accreditation::class, AccreditationPolicy::class);
        Gate::policy(DocumentGenerationRequest::class, DocumentGenerationRequestPolicy::class);
        Gate::policy(AccreditationScoreSnapshot::class, AccreditationScoreSnapshotPolicy::class);
        Gate::policy(AccreditationSubmission::class, AccreditationSubmissionPolicy::class);
        Gate::policy(AmiCycle::class, AmiCyclePolicy::class);
        Gate::policy(AmiFinding::class, AmiFindingPolicy::class);
        Gate::policy(AmiChecklistItem::class, AmiChecklistItemPolicy::class);
        Gate::policy(RtmMeeting::class, RtmMeetingPolicy::class);
        Gate::policy(RtmDecision::class, RtmDecisionPolicy::class);
        Gate::policy(UserTenantScope::class, UserTenantScopePolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);

        foreach ([
            [\App\Models\AccreditationBody::class, \App\Policies\AccreditationBodyPolicy::class],
            [\App\Models\AccreditationCriterion::class, \App\Policies\AccreditationCriterionPolicy::class],
            [\App\Models\AssessmentCriterion::class, \App\Policies\AssessmentCriterionPolicy::class],
            [\App\Models\AssessmentElement::class, \App\Policies\AssessmentElementPolicy::class],
            [\App\Models\AssessmentIndicator::class, \App\Policies\AssessmentIndicatorPolicy::class],
            [\App\Models\AssessmentRubric::class, \App\Policies\AssessmentRubricPolicy::class],
            [\App\Models\AssessmentScale::class, \App\Policies\AssessmentScalePolicy::class],
            [\App\Models\AssessmentThreshold::class, \App\Policies\AssessmentThresholdPolicy::class],
            [\App\Models\AuditLog::class, \App\Policies\AuditLogPolicy::class],
            [\App\Models\EvidenceCollection::class, \App\Policies\EvidenceCollectionPolicy::class],
            [\App\Models\InstrumentFamily::class, \App\Policies\InstrumentFamilyPolicy::class],
            [\App\Models\InstrumentMapping::class, \App\Policies\InstrumentMappingPolicy::class],
            [\App\Models\InstrumentNode::class, \App\Policies\InstrumentNodePolicy::class],
            [\App\Models\InstrumentScoringRule::class, \App\Policies\InstrumentScoringRulePolicy::class],
            [\App\Models\InstrumentVersion::class, \App\Policies\InstrumentVersionPolicy::class],
            [\App\Models\LedTemplate::class, \App\Policies\LedTemplatePolicy::class],
            [\App\Models\LkpsTemplate::class, \App\Policies\LkpsTemplatePolicy::class],
            [\App\Models\ReadinessRun::class, \App\Policies\ReadinessRunPolicy::class],
            [\App\Models\SpmiEvaluation::class, \App\Policies\SpmiEvaluationPolicy::class],
            [\App\Models\SpmiFramework::class, \App\Policies\SpmiFrameworkPolicy::class],
            [\App\Models\SpmiIndicator::class, \App\Policies\SpmiIndicatorPolicy::class],
            [\App\Models\SpmiRealization::class, \App\Policies\SpmiRealizationPolicy::class],
            [\App\Models\SpmiTarget::class, \App\Policies\SpmiTargetPolicy::class],
            [\Spatie\Permission\Models\Role::class, \App\Policies\RolePolicy::class],
        ] as [$model, $policy]) {
            Gate::policy($model, $policy);
        }
    }
}
