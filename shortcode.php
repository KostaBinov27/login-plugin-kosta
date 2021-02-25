<div class="container">
    <div class="form-group">
        <label for="login_email_kbplugin">Email address:</label>
        <input type="email" class="form-control" id="login_email_kbplugin" name="login_email_kbplugin" placeholder="email@example.com">
    </div>
    <div class="form-group">
        <label for="login_pass_kbplugin">Password:</label>
        <input type="password" class="form-control" id="login_pass_kbplugin" name="login_pass_kbplugin" >
    </div>
    <div class="form-group">
        <input hidden id="checkerkb">
        <input hidden id="ipAddress" value="<?php echo $ip; ?>">
    </div>
    <button class="btn btn-success w-50" id="submitLoginFormKB"> Login </button>

    <div class="alert alert-danger d-none mt-3" id="notHuman" role="alert">
        Your are not human!
    </div>
    <div class="alert alert-success d-none mt-3" id="success" role="alert">
        Successfully loged in!
    </div>
    <div class="alert alert-danger d-none mt-3" id="passworng" role="alert">
        Wrong Login Credentials 
    </div>
    <div class="alert alert-danger d-none mt-3" id="locked" role="alert">
        You have been locked for 30 min!
    </div>
</div>