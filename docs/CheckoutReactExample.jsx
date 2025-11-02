import React, { useState } from 'react';

export default function Checkout() {
  const [paymentMethod, setPaymentMethod] = useState('cod');
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [result, setResult] = useState(null);

  async function placeOrder() {
    setLoading(true);
    setMessage('Creating order...');
    try {
      const orderRes = await fetch('/api/orders/create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          cart_items: [/* { product_id, quantity } */],
          delivery_address_id: 1,
          delivery_slot: '2025-10-29 10:00-12:00',
          payment_method: paymentMethod,
        })
      });
      const orderData = await orderRes.json();
      if (!orderData.success) throw new Error(orderData.error || 'Order create failed');

      if (paymentMethod === 'cod') {
        setResult({
          order_id: orderData.order_id,
          payment_method: paymentMethod,
          transaction_id: null,
          message: 'Order placed. Pay cash on delivery.'
        });
        setMessage('');
        setLoading(false);
        return;
      }

      setMessage('Initiating payment...');
      const initRes = await fetch('/api/payments/initiate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderData.order_id, method: paymentMethod })
      });
      const initData = await initRes.json();
      if (!initData.success) throw new Error(initData.error || 'Payment initiation failed');

      // In production you would redirect to initData.redirectUrl
      // window.location.href = initData.redirectUrl;

      setMessage('Confirming payment...');
      const confirmRes = await fetch('/api/payments/confirm', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ order_id: orderData.order_id })
      });
      const confirmData = await confirmRes.json();
      if (!confirmData.success) throw new Error(confirmData.message || 'Payment failed');

      setResult({
        order_id: orderData.order_id,
        payment_method: paymentMethod,
        transaction_id: confirmData.transaction_id,
        message: 'We will deliver your order in your selected slot.'
      });
      setMessage('');
    } catch (e) {
      setMessage(e.message);
    } finally {
      setLoading(false);
    }
  }

  return (
    <div>
      <h3>Checkout</h3>
      <div>
        <label><input type="radio" name="pm" value="bkash" onChange={() => setPaymentMethod('bkash')} /> bKash</label>
        <label><input type="radio" name="pm" value="nagad" onChange={() => setPaymentMethod('nagad')} /> Nagad</label>
        <label><input type="radio" name="pm" value="card" onChange={() => setPaymentMethod('card')} /> Card</label>
        <label><input type="radio" name="pm" value="cod" defaultChecked onChange={() => setPaymentMethod('cod')} /> Cash on Delivery</label>
      </div>
      <button disabled={loading} onClick={placeOrder}>{loading ? 'Processing...' : 'Place Order'}</button>
      {message && <p>{message}</p>}
      {result && (
        <div>
          <p>Order ID: {result.order_id}</p>
          <p>Payment method: {result.payment_method}</p>
          {result.transaction_id && <p>Transaction ID: {result.transaction_id}</p>}
          <p>{result.message}</p>
        </div>
      )}
    </div>
  );
}


