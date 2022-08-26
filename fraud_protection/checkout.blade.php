@extends('layout.mainlayout')

@section('title')
Checkout |&nbsp @php echo $global['name'] @endphp
@endsection

@section('header')
@php echo $global['head_script'];  @endphp
@php  echo $global['meta_description']  @endphp
@endsection

@section('content')

<!-- Inner Header -->
<div class="cnp_inside" id="innerheader_background">
	<div class="inner_overlay"></div>
	<div class="cnp_container">
		@include('layout.innerHeader')

		<div class="innerheader_content">
			<h1>Checkout</h1>
		</div>
	</div>
</div>
<!-- Inner Header End -->

<!-- Checkout -->
<div class="cnp_inside">
	<div class="cnp_container">
		<div class="checkout_flex mtb60">
			@if (count($cart_items) == 0)
			<p class="cart_checkout">Checkout is not available when cart is empty</p>
			@else
			<div class="checkout_left">
				{{-- @if(!(Session::has('userlogin'))) --}}
				{{-- dd(Auth::user()->role) --}}
				@if(!Auth::check() || ( Auth::check() && Auth::user()->role=='admin' ))
				<div class="click_login">
					@php $errorFound=$errors->first()  @endphp
					<button class="login_accordion @if(!empty($errorFound)) active @endif">Click here to login</button>
					<div class="login_content" @if(!empty($errorFound)) style="max-height: 293px;"  @endif>
						<p>If you have shopped with us before, please enter your details in the boxes below. If you are a new customer, please proceed to the Billing & Shipping section.</p>
						<form name="abc" action="{{ route('doUserLogin') }}" method="POST">
							@csrf
							<div class="login_form_flex">
								<div class="checkout_fields label_design mr30">
			                        <label for="email">Email Address</label>
			                    	<input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

		                        @error('email')
		                            <span class="invalid-feedback" role="alert">
		                                <strong>{{ $message }}</strong>
		                            </span>
		                        @enderror
	                        	</div>

		                        <div class="checkout_fields label_design">
			                        <label for="password">Password</label>
			                    	<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

			                        @error('password')
			                            <span class="invalid-feedback" role="alert">
			                                <strong>{{ $message }}</strong>
			                            </span>
			                        @enderror
		                        </div>
							</div>

	                        <div class="checkout_btn label_design mtb0">
		                    	<button type="submit" id="login_submit">Login</button>
	                    	</div>

			                <div class="forget_password checkout_password">
				                <a href="{{ route('password.request') }}">forget password?</a>
			                </div>
			                @if($errors->any())
			                    <!-- <h4>{{$errors->first()}}</h4> -->
			                    <div class="alert_error">
			                    	<p>{{$errors->first()}}</p>
			                    </div>
			                @endif
						</form>
					</div>
				</div>
				
				@endif
				<div class="billing_shipping">
					<h2>Billing & Shipping</h2>
					<form action="{{ route('doPayment')}}" method="post" id="checkout_form">
						@csrf
						<input type="hidden" name="shippingType">
						<div class="checkout_form">
							<div class="register_fields_flex">
								<input id="user_id" type="hidden" name="user_id" value="@isset($user->id){{$user->id}} @endisset">
	                        	<div class="register_fields label_design field_width rsp_mr20 mr30">
			                        <label for="first_name">First Name</label>
			                    	<input id="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" name="first_name" value="@isset($user->first_name){{$user->first_name}} @endisset">

			                    @error('first_name')
                            		<span class="invalid-feedback" role="alert">
                                		<strong>{{ $message }}</strong>
                          			</span>
                       			@enderror
	                        	</div>
 
		                         <div class="register_fields label_design field_width">
			                        <label for="last_name">Last Name</label>
			                    	<input id="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="@isset($user->last_name){{$user->last_name}} @endisset">
			                    @error('last_name')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            	@enderror
		                        </div>
	                        </div>

							<div class="register_fields label_design">
		                        <label for="name">Email Address</label>
		                    	<input id="email" type="text" name="email" class="form-control @error('email') is-invalid @enderror" value="@isset($user->email){{$user->email}} @endisset">

			                    @error('email')
	                            <span class="invalid-feedback" role="alert">
	                                <strong>{{ $message }}</strong>
	                            </span>
	                        	@enderror
	                        </div>

	                        <div class="register_fields label_design email_phone">
		                        <label for="phone">Phone</label>
		                    	<input type="phone" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="@isset($user->phone){{$user->phone}}@endisset">

		                    @error('phone')
                            	<span class="invalid-feedback" role="alert">
                                	<strong>{{ $message }}</strong>
                            	</span>
                        	@enderror
	                        </div>
							<div class="register_fields_flex">
		                        <div class="register_fields label_design field_width rsp_mr20 mr30">
			                        <label for="billing_postcode">Post Code</label>
			                    	<input id="billing_postcode" type="text" class="form-control @error('post_code') is-invalid @enderror" name="post_code" name="post_code" value="@isset($user->post_code){{$user->post_code}}@endisset" autocomplete="off">
		                    	@error('post_code')
                            		<span class="invalid-feedback" role="alert">
                                		<strong>{{ $message }}</strong>
                            		</span>
                        		@enderror
	                        	</div>
	                        	<button type="button" id="searchAddressbtn" class="search_accordion">Search Address</button>
	                        </div>
	                        <div class="register_fields label_design">
		                        <select id="curr_sel_add" style="display: none"></select>
		                    </div>

	                        	<div class="register_fields label_design">
			                        <label for="billing_city">City</label>
			                    	<input id="billing_city" name="city" class="form-control @error('city') is-invalid @enderror" type="text" value="@isset($user->city){{$user->city}}@endisset">
			                    @error('city')
                                	<span class="invalid-feedback" role="alert">
                                    	<strong>{{ $message }}</strong>
                                	</span>
                            	@enderror
		                        </div>
				
							<div class="register_fields_flex">
		                       	<div class="register_fields label_design field_width rsp_mr20 mr30">
			                        <label for="address_1">Address line 1</label>
			                    	<input id="billing_address_1" type="text" class="form-control @error('address_1') is-invalid @enderror" name="address_1" value="@isset($user->address_1){{$user->address_1}}@endisset">
			                    @error('address_1')
                                	<span class="invalid-feedback" role="alert">
                                    	<strong>{{ $message }}</strong>
                                	</span>
                            	@enderror
		                        </div>

		                        <div class="register_fields label_design field_width">
			                        <label for="billing_address_2">Address line 2</label>
			                    	<input id="billing_address_2" type="text" class="form-control @error('address_2') is-invalid @enderror" name="address_2" value="@isset($user->address_2){{$user->address_2}}@endisset">
			                    @error('address_2')
                                	<span class="invalid-feedback" role="alert">
                                    	<strong>{{ $message }}</strong>
                                	</span>
                            	@enderror
		                        </div>
	                       	</div>
							
	                        <div class="register_fields label_design">
			                        <label for="country">Country</label>
			                    	<select class="selector" id="country" class="form-control @error('country') is-invalid @enderror" name="country">
										<option value="UK" @isset($user->country) @if($user->country == "UK")? 'selected' : '' @endif @endisset>UK</option>
                                <option value="germany" @isset($user->country) @if($user->country == "germany")? 'selected' : '' @endif @endisset>Germany</option>
                                <option value="france" @isset($user->country) @if($user->country == "france")? 'selected' : '' @endif @endisset>France</option>
									</select>
								@error('country')
                                	<span class="invalid-feedback" role="alert">
                                    	<strong>{{ $message }}</strong>
                                	</span>
                            	@enderror
		                    </div>

							<!-- {{-- @if(!(Session::has('userlogin'))) --}}
							@if(!Auth::check() || ( Auth::check() && Auth::user()->role=='admin' ))
							<div class="register_fields label_design email_phone">
			                    <label for="password">Password</label>
			                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
	                        </div>

	                        @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror

							@endif -->

							
							@if(!(Session::has('userlogin')))
							<!-- <p>Create an account by entering the information below. If you are a returning customer please login at the top of the page.</p> -->
							@endif

							

	                        <div class="shipping_btn label_design desktop_btn">
		                    	<button type="submit" id="proceedShipping_btn">Proceed To Payment</button>
	                        </div>
						</div>
					</form>
				</div>
			</div>

			<div class="checkout_total">
					<div class="shipping_method">
	                	<span>Shipping Options</span>
	                	<div class="shipping_method_list">
	                		<div class="method_list">
	                			<label class="radio_container"> 
	                    			<p>@php 
											echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_royal_mail_1st_class','label','shipping')
										@endphp</p>
							  		@php 
										echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_royal_mail_1st_class','values','shipping')
									@endphp
									<span class="checkmark"></span>
								</label>
	                		</div>

	                		<div class="method_list">
	                			<label class="radio_container"> 
	                    			<p>@php 
											echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_royal_mail_2nd_class','label','shipping')
										@endphp</p>
							  		@php 
										echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_royal_mail_2nd_class','values','shipping')
									@endphp
							  		<span class="checkmark"></span>
								</label>
	                		</div>

	                		<div class="method_list">
	                			<label class="radio_container"> 
	                    			<p>@php 
											echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_saturday_guaranteed_delivery','label','shipping')
										@endphp</p>
							  		@php 
										echo App\Http\Controllers\AdminOptionController::getRadioValues('shipping_saturday_guaranteed_delivery','values','shipping')
									@endphp
							  		<span class="checkmark"></span>
								</label>
	                		</div>
	                	</div>
		            </div>

					<div class="order_details">
						<h3>Order summary</h3>
					</div>
					<div class="out_border">
						@isset($cart_items['plates'])
						@foreach($cart_items['plates'] as $cart_key => $cart_item)
						<div class="product_inner">
							<div class="product_details_inner">
								<span>Registration: {{ strtoupper($cart_item['registration']) }}</span>
							</div>
							
							<div class="quantity_inner">
								<span><i class="fas fa-times"></i> {{ $cart_item['product_qty']}} </span>
							</div>

							<div class="product_price_inner">
								£@php 
									echo \App\Helpers\AppHelper::instance()->getCartPlateTotalPrice($cart_key);
								@endphp
							</div>
						</div>
						@endforeach
						@endisset

						@isset($cart_items['addons'])
						@foreach($cart_items['addons'] as $cart_key => $cart_item)
						<div class="product_inner">
							<div class="product_details_inner">						
								<span class="text_over">
									Addon: 
									@php 
										echo App\Http\Controllers\cartController::getAddonDetails($cart_item['addon_id'],'name')
									@endphp
								</span>
							</div>

							<div class="quantity_inner">
								<span><i class="fas fa-times"></i> {{ $cart_item['addon_qty']}} </span>
							</div>
						
							<div class="product_price_inner">
								£@php 
									echo \App\Helpers\AppHelper::instance()->getCartAddonsPrice($cart_key);
								@endphp
							</div>
						</div>
						@endforeach
						@endisset
					

						<div class="subtotal_wrap">
							<div class="product_inner border_bottom ptb_0">
								<div class="product_details_inner">
									<p><b>Subtotal</b></p>
								</div>

								<div class="product_price_inner">
									£<span class="sub_total">@php 
										echo \App\Helpers\AppHelper::instance()->getCartTotalPrice();
									@endphp</span>
								</div>
							</div>

							<div class="product_inner border_bottom ptb_0">
								<div class="product_details_inner">
									<p><b>Shipping Cost</b></p>
								</div>

								<div class="product_price_inner">
									£<span class="shipping_cost">0.00</span>
								</div>
							</div>

							<div class="product_inner ptb_0 border_bottom">
								<div class="product_details_inner">
									<p><b>Total</b></p>
								</div>

								<div class="product_price_inner">
									£<span class="total_price">0.00</span>
								</div>
							</div>
						</div>
					</div><br>
			
				<div class="shipping_method">
	                	 <img src="{{ asset('images/stripe-badge-transparent.png') }}" alt="Quote Icon">
		            </div>
					
				</div>
			
			@endif
			<div class="shipping_btn label_design mobile_btn">
        	<button type="submit" id="proceedShipping_btn">Proceed To Payment</button>
        </div>
		</div>

	</div>
</div>
<!-- Checkout End-->
<script>
	var acc = document.getElementsByClassName("login_accordion");
	var i;
	for (i = 0; i < acc.length; i++) {
	acc[i].addEventListener("click", function() {
	this.classList.toggle("active");
	var panel = this.nextElementSibling;
	if (panel.style.maxHeight) {
	panel.style.maxHeight = null;
	} else {
	panel.style.maxHeight = panel.scrollHeight + "px";
	} 
});
}
</script>
<script type="text/javascript">
	$(document).ready(function(){
		calaulatePrice();
		$('input[type=radio][name=shipping_type]').change(function() {
	    	calaulatePrice();
		});
	});
	function calaulatePrice(){
		var price = $('input[type=radio][name="shipping_type"]:checked').attr("data-sale-price");
		var value = $('input[type=radio][name="shipping_type"]:checked').val();
		$('input[type=hidden][name="shippingType"]').val(value);
		price=parseFloat(price);
		var subtotal=parseFloat($(".sub_total").html());
		var totalPrice=price+subtotal;
		totalPrice=parseFloat(totalPrice).toFixed(2);
		$(".shipping_cost").html(price);
		$(".total_price").html(totalPrice);
	}
</script>
<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script type="text/javascript">
	$('#searchAddressbtn').click(function(e) {
		var postcode=$('#billing_postcode').val();
		if (postcode == null || postcode.length == 0 || postcode == undefined || postcode == '') {
			return alert('Please Enter Post Code');
		}	
		$(this).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
		$(this).attr('disabled',true);
    	$('#curr_sel_add').html('');
        var code = $('#billing_postcode').val();
        $.ajax({
            url: "{{route('addressfetch')}}",
            type: 'post',
            dataType: 'json',
            data: {
                _token:"{{csrf_token()}}" ,
                custom_action: 'postcode_verification',
                post_code : code
            },            
            success: function(result) {	
            	console.log(result);
            	$('#searchAddressbtn').text('Search Address');
            	$('#searchAddressbtn').attr('disabled',false);
                if(result)
                {
                    $('#curr_sel_add').append('<option selected disabled>Select Address</option>');
                    for(var i=0; i<result.length; i++)
                    {
                        $('#curr_sel_add').append('<option value="'+result[i]['thoroughfare']+","+result[i]['locality']+","+result[i]['district']+","+result[i]['county']+","+result[i]['postcode']+'">'+result[i]['thoroughfare']+","+result[i]['locality']+","+result[i]['district']+","+result[i]['county']+'</option>');
                        $('#curr_sel_add').show();
                    }                            
                }
                else
                {
                    console.log('false');
                }
            },
            error:function (xhr, ajaxOptions, thrownError) {
            	$('#searchAddressbtn').text('Search Address');
            	$('#searchAddressbtn').attr('disabled',false);
            	$(this).text('Search Address');
		        alert(thrownError);
      		}
        });
    });

	$(".shipping_btn").click(function(e){
		e.preventDefault();
		$('#checkout_form').submit();
	});
</script>
@endsection