<?php
/**
 * @author      Antoons Miguel
 * @package     Joomla.Administrator
 * @subpackage  com_bramscampaign
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<div class="container custom_container container_margin">
	<?php echo '<input id="token" type="hidden" name="' . JSession::getFormToken() . '" value="1" />'; ?>
	<div class='row'>
		<div class='col custom_col'>
			<button
				type='button'
				class='customBtn return'
				onclick='window.location.href="/index.php?option=com_bramscampaign&view=campaigns"'
			>
				<i class="fa fa-arrow-left" aria-hidden="true"></i>
				Return
			</button>
			<h1 id="title">Create New Campaign</h1>
			<p id='error'>

			</p>
			<div id='inputContainer'>
				<label id='name' class='form-label required' for='campaignName'>Name</label>
				<input
					type='text'
					class='form-control'
					id="campaignName"
					maxlength="127"
					required
				>

				<label class='form-label required' for='campaignType'>Type</label>
				<select
					name='types'
					class='form-control'
					id="campaignType"
					required
				>
                    <!-- type options will come here -->
				</select>

                <label class='form-label required' for='campaignStation'>Station</label>
                <select
                        name='stations'
                        class='form-control'
                        id="campaignStation"
                        required
                >
                    <!-- station options will come here -->
                </select>

                <label class='form-label required' id="start" for='campaignStart'>Start</label>
                <input
                        class='form-control'
                        type='datetime-local'
                        id='campaignStart'
                        required
                >

                <label class='form-label required' id="end" for='campaignEnd'>End</label>
                <input
                        class='form-control'
                        type='datetime-local'
                        id="campaignEnd"
                        required
                >

				<label class='form-label' id="FFT" for='campaignFFT'>FFT</label>
				<input
					class='form-control'
					type='number'
					value='16384'
					min='0'
					max="99999999999"
					id="campaignFFT"
				>

                <label class='form-label' id="overlap" for='campaignOverlap'>Overlap</label>
                <input
                    class='form-control'
                    type='number'
                    value='14488'
                    min='0'
                    max="99999999999"
                    id="campaignOverlap"
                >

                <label class='form-label' id="Color Min" for='campaignColorMin'>Color Min</label>
                <input
                    class='form-control'
                    type='number'
                    step="any"
                    min='0'
                    max="99999999999"
                    id="campaignColorMin"
                >

                <label class='form-label' id="Color Max" for='campaignColorMax'>Color Max</label>
                <input
                    class='form-control'
                    type='number'
                    step="any"
                    min='0'
                    max="99999999999"
                    id="campaignColorMax"
                >

				<label for='campaignComments'>Comments</label>
				<input
					class='form-control'
					type='text'
					id='campaignComments'
                    maxlength="255"
				>

                <div id="buttonContainer">
                    <button
                        name='submit'
                        class='customBtn save'
                        id='submit'
                        onclick="formProcess(document.getElementById('inputContainer').children)"
                    >
                        <i class="fa fa-floppy-o" aria-hidden="true"></i>
                        Save
                    </button>
                    <span id="spinner" class="spinner-border text-success"></span>
                </div>
			</div>
		</div>
	</div>
</div>
