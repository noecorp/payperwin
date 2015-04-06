<?php namespace App\Models;

class Deposit extends Model {
	const OTHER_CODE = 0;
	const COMPLETED_CODE = 1;
	const REVERSED_CODE = 3;
	const CANCELED_REVERSAL_CODE = 5;
	const REFUNDED_CODE = 7;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'deposits';
	protected $dates = ['payment_date'];

	protected $fillable = ['user_id', 'payment_provider', 'transaction_id', 'parent_transaction_id', 'gross', 'fee', 'email', 'payment_date', 'status', 'source_message','processed','status_code'];

	public function user() {
		return $this->belongsTo(User::class);
	}

	public function isCompleted() {
		return $this->status_code==Deposit::COMPLETED_CODE;
	}

	public function isOther() {
		return $this->status_code==Deposit::OTHER_CODE;
	}

	public function isReversed() {
		return $this->status_code==Deposit::REVERSED_CODE;
	}

	public function isReversalCanceled() {
		return $this->status_code==Deposit::CANCELED_REVERSAL_CODE;
	}

	public function isRefunded() {
		return $this->status_code==Deposit::REFUNDED_CODE;
	}


}
