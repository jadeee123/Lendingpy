<?php
session_start();

$error = "";
$result = $_SESSION['result'] ?? null;
$amountValue = $_SESSION['resultInput']['amount'] ?? '';
$monthsValue = $_SESSION['resultInput']['months'] ?? '';

// Handle Clear button
if (isset($_POST['clear'])) {
    unset($_SESSION['result']);
    unset($_SESSION['resultInput']);
    $error = "";
    header("Location: " . $_SERVER['PHP_SELF']); // redirect to reset POST
    exit;
}

// Handle Compute button
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['clear'])) {
    $amount = $_POST["amount"];
    $months = $_POST["months"];

    // Save input values to session so they stay
    $_SESSION['resultInput'] = [
        'amount' => $amount,
        'months' => $months
    ];

    if ($amount < 500) {
        $error = "Minimum loan amount is ₱500.";
    } elseif ($amount > 50000) {
        $error = "Maximum loan amount is ₱50,000.";
    } else {

        $annualRate = 0.12;
        $monthlyRate = $annualRate / 12;

        $P = $amount;
        $i = $monthlyRate;
        $n = $months;

        // Amortized loan formula
        $paymentPerMonth = $P * ($i * pow(1 + $i, $n)) / (pow(1 + $i, $n) - 1);
        $totalAmount = $paymentPerMonth * $n;
        $totalInterest = $totalAmount - $P;
        $monthlyInterest = $P * $i;

        // Store formatted results in session
        $_SESSION['result'] = [
            'monthlyInterest' => number_format($monthlyInterest, 2),
            'totalInterest'   => number_format($totalInterest, 2),
            'totalAmount'     => number_format($totalAmount, 2),
            'paymentPerMonth' => number_format($paymentPerMonth, 2)
        ];

        // Redirect to prevent form resubmit
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lending System</title>
    <style>
        body {
            font-family: Arial;
            background: #eee;
        }
        .box {
            width: 380px;
            margin: 80px auto;
            padding: 20px;
            background: white;
            border: 1px solid #ccc;
        }
        input, select, button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        .error {
            color: red;
            margin-top: 10px;
        }
        .result {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #aaa;
            background: #f9f9f9;
        }
        .buttons {
            display: flex;
            gap: 10px;
        }
        .buttons button {
            flex: 1;
        }
    </style>
</head>

<body>

<div class="box">
    <h3>Lending System</h3>

    <form method="post">
        Loan Amount (₱500 – ₱50,000)
        <input type="number" name="amount" required 
               value="<?php echo htmlspecialchars($amountValue); ?>">

        Loan Term
        <select name="months" required>
            <option value="">-- Select Months --</option>
            <option value="1"  <?php if($monthsValue == 1) echo 'selected'; ?>>1 Month</option>
            <option value="3"  <?php if($monthsValue == 3) echo 'selected'; ?>>3 Months</option>
            <option value="6"  <?php if($monthsValue == 6) echo 'selected'; ?>>6 Months</option>
            <option value="9"  <?php if($monthsValue == 9) echo 'selected'; ?>>9 Months</option>
            <option value="12" <?php if($monthsValue == 12) echo 'selected'; ?>>12 Months</option>
            <option value="24" <?php if($monthsValue == 24) echo 'selected'; ?>>24 Months</option>
        </select>

        <div class="buttons">
            <button type="submit">Compute</button>
            <button type="submit" name="clear" value="1">Clear</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <div class="result">
            Monthly Interest: ₱<?php echo $result['monthlyInterest']; ?><br>
            Total Interest: ₱<?php echo $result['totalInterest']; ?><br>
            Total Amount to Pay: ₱<?php echo $result['totalAmount']; ?><br>
            Payment per Month: ₱<?php echo $result['paymentPerMonth']; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
