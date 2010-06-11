<?php
function wpsc_options_taxes(){
global $wpdb;
?>
<form name='cart_options' id='cart_options' method='post' action=''>
<div class="wrap">
	<h2>Tax Settings</h2>
	<p>

		<label for='wpsc_tax_on'>
			<input type="checkbox" value='1' checked='checked' id='wpsc_turn_tax_on' name='wpsc_options[turn_tax_on]' />
			Turn tax on
		</label>

	</p>
	<p>

		<label for='wpsc_tax_shipping'>
			<input type="checkbox" value='' id='wpsc_tax_shipping' name='wpsc_options[tax_shipping]' />
		Apply tax to shipping and handling
		</label>

	</p>
	<p>

		<label for='wpsc_tax_inprice1'>
			<input type="radio" value='1' checked='checked' id='wpsc_tax_inprice1' name='wpsc_options[tax_inprice]' />
			Product prices are tax exclusive - add tax to the price during checkout
		</label>

	</p>
	<p>

		<label for='wpsc_tax_inprice2'>
			<input type="radio" value='0' checked='checked' id='wpsc_tax_inprice2' name='wpsc_options[tax_inprice]' />
			Product prices are tax inclusive - during checkout the total price doesn't increase but tax is shown as a line item
		</label>

	</p>	
	
	<h4>Product Specific Tax</h4>
	<p>
		<label for='wpsc_tax_product_1'>
			<input type="radio" value='' checked='checked' id='wpsc_tax_product_1' name='wpsc_options[tax_product]' />
		Add per product tax to tax percentage if product has a specific tax rate
		</label>

	</p>	
	<p>
		<label for='wpsc_tax_product_2'>
			<input type="radio" value='' id='wpsc_tax_product_2' name='wpsc_options[tax_product]' />
		Replace tax percentage with product specific tax rate
		</label>

	</p>	
	
	<h4>Tax Logic</h4>
	<p>
		
		<label for='wpsc_tax_logic_1'>
			<input type="radio" value='0' checked='checked' id='wpsc_tax_logic_1' name='wpsc_options[tax_logic]' />
		Apply tax when Billing and Shipping Country is the same as Shops base location
		</label>

	</p>
	<p>

		<label for='wpsc_tax_logic_2'>
			<input type="radio" value='1' id='wpsc_tax_logic_2' name='wpsc_options[tax_logic]' />
		Apply tax when Shipping Country is the same as Shops base location
		</label>

	</p>
	<p>

		<label for='wpsc_tax_logic_3'>
			<input type="radio" value='2' id='wpsc_tax_logic_3' name='wpsc_options[tax_logic]' />
		Apply tax when Billing Country is the same as Shops base location
		</label>

	</p>	
	
	<h4>Tax Rates</h4>
	<p>
		<input type='text' size='4' name='wpsc_options[tax_rate][0][rate]' value='12.5' /> %
		<select class="country" id="country-0" name="wpsc_options[tax_rates][0][country]">
		<option value="*">All Markets</option><option value="CA">Canada</option><option selected="selected" value="US">USA</option><option value="GB">United Kingdom</option><option value="AR" disabled="">Argentina</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="BS">Bahamas</option><option value="BE">Belgium</option><option value="BR">Brazil</option><option value="BG">Bulgaria</option><option value="CL">Chile</option><option value="CN">China</option><option value="CO">Colombia</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="EC">Ecuador</option><option value="EE">Estonia</option><option value="FI">Finland</option><option value="FR">France</option><option value="DE">Germany</option><option value="GR">Greece</option><option value="GP">Guadeloupe</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IE">Ireland</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="LV">Latvia</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MY">Malaysia</option><option value="MT">Malta</option><option value="MX">Mexico</option><option value="NL">Netherlands</option><option value="NZ">New Zealand</option><option value="NO">Norway</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="VC">St. Vincent</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="SY">Syria</option><option value="TW">Taiwan</option><option value="TH">Thailand</option><option value="TT">Trinidad and Tobago</option><option value="TR">Turkey</option><option value="AE">United Arab Emirates</option><option value="UY">Uruguay</option><option value="VE">Venezuela</option></select>
		<select class="zone" id="zone-0" name="wpsc_options[tax_rates][0][zone]">
		<option value="AL" disabled="">Alabama</option><option value="AK">Alaska </option><option value="AZ">Arizona</option><option value="AR">Arkansas</option><option value="CA">California </option><option value="CO">Colorado</option><option value="CT">Connecticut</option><option value="DE">Delaware</option><option value="DC">District Of Columbia </option><option value="FL">Florida</option><option value="GA">Georgia </option><option value="HI">Hawaii</option><option value="ID">Idaho</option><option value="IL">Illinois</option><option value="IN">Indiana</option><option value="IA">Iowa</option><option value="KS">Kansas</option><option value="KY">Kentucky</option><option value="LA">Louisiana</option><option value="ME">Maine</option><option value="MD">Maryland</option><option value="MA">Massachusetts</option><option value="MI">Michigan</option><option value="MN">Minnesota</option><option value="MS">Mississippi</option><option value="MO">Missouri</option><option value="MT">Montana</option><option value="NE">Nebraska</option><option value="NV">Nevada</option><option value="NH">New Hampshire</option><option value="NJ">New Jersey</option><option value="NM">New Mexico</option><option value="NY">New York</option><option value="NC">North Carolina</option><option value="ND">North Dakota</option><option value="OH">Ohio</option><option value="OK">Oklahoma</option><option value="OR">Oregon</option><option value="PA">Pennsylvania</option><option value="RI">Rhode Island</option><option value="SC">South Carolina</option><option value="SD">South Dakota</option><option value="TN">Tennessee</option><option value="TX">Texas</option><option value="UT">Utah</option><option value="VT">Vermont</option><option value="VA">Virginia</option><option value="WA">Washington</option><option value="WV">West Virginia</option><option value="WI">Wisconsin</option><option value="WY">Wyoming</option></select>
	<a href=''>Delete</a>
	</p>
	<p>
		<input type='text' size='4' name='wpsc_options[tax_rate][0][rate]' value='15' /> %
		<select class="country" id="country-0" name="wpsc_options[tax_rates][0][country]">
		<option value="*">All Markets</option><option value="CA">Canada</option><option selected="selected" value="US">USA</option><option value="GB">United Kingdom</option><option value="AR" disabled="">Argentina</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="BS">Bahamas</option><option value="BE">Belgium</option><option value="BR">Brazil</option><option value="BG">Bulgaria</option><option value="CL">Chile</option><option value="CN">China</option><option value="CO">Colombia</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="DK">Denmark</option><option value="EC">Ecuador</option><option value="EE">Estonia</option><option value="FI">Finland</option><option value="FR">France</option><option value="DE">Germany</option><option value="GR">Greece</option><option value="GP">Guadeloupe</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IE">Ireland</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="LV">Latvia</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MY">Malaysia</option><option value="MT">Malta</option><option value="MX">Mexico</option><option value="NL">Netherlands</option><option value="NZ">New Zealand</option><option value="NO">Norway</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="SG">Singapore</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="ZA">South Africa</option><option value="KR">South Korea</option><option value="ES">Spain</option><option value="VC">St. Vincent</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="SY">Syria</option><option value="TW">Taiwan</option><option value="TH">Thailand</option><option value="TT">Trinidad and Tobago</option><option value="TR">Turkey</option><option value="AE">United Arab Emirates</option><option value="UY">Uruguay</option><option value="VE">Venezuela</option></select>
		
	<a href=''>Delete</a>
	</p>
	<p>
		<a href=''>Add New Tax Rate</a>
	</p>
	<p>
		<input type='submit' class='button-primary' value='Save Changes' name='submit_tax' />
	</p>
</div>
</form>
<?php
}