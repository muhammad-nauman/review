<?php
namespace DTApi\Http\Controllers;

use App\Http\Requests\CreateBookingRequest;
use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * @var BookingRepository
     */
    protected $repository;
    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }
    /**
     * @return mixed
     */
    public function index()
    {
        // This will make sure the code wont break
        $response = [];
        if(request()->has('user_id')) {
            $response = $this->repository->getUsersJobs(request('user_id'));
        }
        if(in_array(request()->__authenticatedUser->user_type, [env('ADMIN_ROLE_ID'), env('SUPERADMIN_ROLE_ID')]))
        {
            $response = $this->repository->getAll(request());
        }
        return response($response);
    }
    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);
        return response($job);
    }
    /**
     * @param CreateBookingRequest $request
     * @return mixed
     */
    public function store(CreateBookingRequest $request)
    {
        // with the help of request() helper function you can access request anywhere within the request life cycle so don't need to initialize here and we can have 1 less param for store method
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);
        return response($response);
    }
    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);
        return response($response);
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);
        return response($response);
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }
        return null;
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJob($data, $user);
        return response($response);
    }
    public function acceptJobWithId()
    {
        $data = request('job_id');
        $user = request()->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);
        return response($response);
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->cancelJobAjax($data, $user);
        return response($response);
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);
        return response($response);
    }
    public function customerNotCall()
    {
        $response = $this->repository->customerNotCall(request()->all());
        return response($response);
    }
    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs()
    {
        $user = request()->__authenticatedUser;
        $response = $this->repository->getPotentialJobs($user);
        return response($response);
    }
    public function distanceFeed(Request $request)
    {
        $data = $request->all();
        if(!isset($data['admincomment']) || $data['admincomment'] == '') return "Please, add comment";
        $distance = request('distance', ''); // It will return empty string when no value exist
        $time = request('time', '');
        $jobid = request('jobid');
        $session = request('session_time', '');
        $flagged = request('flagged') == 'true' ? 'yes' : 'no';
        $manually_handled = $data['manually_handled'] == 'true' ? 'yes' : 'no';
        $by_admin = $data['by_admin'] == 'true' ? 'yes' : 'no';
        $admincomment = request('admincomment'); // already checked for no value
        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update(array('distance' => $distance, 'time' => $time));
        }
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update(array('admin_comments' => $admincomment, 'flagged' => $flagged, 'session_time' => $session, 'manually_handled' => $manually_handled, 'by_admin' => $by_admin));
        }
        return response('Record updated!');
    }
    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);
        return response($response);
    }
    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');
        return response(['success' => 'Push sent']);
    }
    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        try {
            $this->repository->sendSMSNotificationToTranslator($job);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
        return response(['success' => 'SMS sent']);
    }
}
