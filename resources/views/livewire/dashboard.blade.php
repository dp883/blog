<div>
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Dashboard</h2>
        <p class="text-secondary mb-0">
            Welcome to your blog admin panel.
        </p>
    </div>

    <div class="row g-4 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Total Posts</p>
                    <h2 class="fw-bold mb-0">{{ $posts ?? 0 }}</h2>
                    <small class="text-secondary">Blog articles</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Published Posts</p>
                    <h2 class="fw-bold mb-0">{{  $publishedPosts ?? 0 }}</h2>
                    <small class="text-secondary">Live articles</small>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Categories</p>
                    <h2 class="fw-bold mb-0">{{ $categories ?? 0 }}</h2>
                    <small class="text-secondary">Content categories</small>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <p class="text-secondary mb-2">Users</p>
                    <h2 class="fw-bold mb-0">{{ $users ?? 0 }}</h2>
                    <small class="text-secondary">Registered accounts</small>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold">Welcome to your admin panel!</h5>
        </div>
    </div>
</div>