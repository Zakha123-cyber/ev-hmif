<div id="sideBar" class="relative flex flex-col flex-wrap bg-white border-r border-gray-300 p-6 flex-none w-64 md:-ml-64 md:fixed md:top-0 md:z-30 md:h-screen md:shadow-xl animated faster">
    <div class="flex flex-col">
        <div class="text-right hidden md:block mb-4">
            <button id="sideBarHideBtn">
                <i class="fad fa-times-circle"></i>
            </button>
        </div>

        <p class="uppercase text-xs text-gray-600 mb-4 tracking-wider">Dashboard</p>
        <a href="{{url('/back-admin-logged/dashboard')}}" class="mb-3 @if (Request::segment(2) == 'dashboard') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-chart-pie text-xs mr-2"></i>                
            Dashboard
        </a>

        <p class="uppercase text-xs text-gray-600 mb-4 mt-4 tracking-wider">Master Data</p>
        <a href="{{url('/back-admin-logged/candidate')}}" class="mb-3 @if (Request::segment(2) == 'candidate') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-chart-pie text-xs mr-2"></i>
            &nbsp;Candidate
        </a>
        <a href="{{url('/back-admin-logged/participant')}}" class="mb-3 @if (Request::segment(2) == 'participant') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-chart-pie text-xs mr-2"></i>
            &nbsp;Participant
        </a>
        <a href="{{url('/back-admin-logged/participant-verification')}}" class="mb-3 @if (Request::segment(2) == 'participant-verification') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-chart-pie text-xs mr-2"></i>
            &nbsp;Participant Verification
        </a>
        <a href="{{url('/back-admin-logged/live-count')}}" class="mb-3 @if (Request::segment(2) == 'live-count') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-chart-pie text-xs mr-2"></i>
            &nbsp;Live Count
        </a>
        <a href="{{url('/back-admin-logged/election-schedule')}}" class="mb-3 @if (Request::segment(2) == 'election-schedule') text-teal-600 @endif capitalize font-medium text-sm hover:text-teal-600 transition ease-in-out duration-500">
            <i class="fad fa-calendar-alt text-xs mr-2"></i>
            &nbsp;Jadwal Pemilihan
        </a>
    </div>
</div>