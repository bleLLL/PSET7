<?php
    /**
     * Created by PhpStorm.
     * User: T
     * Date: 2015.09.21
     * Time: 14:36
     */


    // configuration
    require("../includes/config.php");

    if ($_SERVER["REQUEST_METHOD"] == "GET")
    {
        render("sell_form.php", ["title" => "Sell"]);
    }
    else if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        //dump($_POST);

        extract($_POST);
        $positions = $_SESSION["portfolio"]["positions"];
        $id = $_SESSION["id"];
        $quote;

        if ($amount < 1)
        {
            apologize("Bad input");
        }

        // check if the amount of shares tried to sell is not bigger than amount of owned shares
        foreach ($positions as $position)
        {
            if ($position["symbol"] === $symbol)
            {
                $quote = $position;
                if ($position["shares"] < $amount)
                {
                    apologize("You don't have so many shares.");
                }
                break;
            }
        }
        $left = $position["shares"] - $amount;
        $symbol = $position["symbol"];
        if ($left > 0)
        {
            $query = "UPDATE stocks SET shares = $left WHERE id = $id AND symbol = '$symbol'";
        }
        else
        {
            $query = "DELETE FROM stocks WHERE id = $id AND symbol = '$symbol'";
        }

        $price = $position["price"];
        $cash = $amount * $price;
        if (query($query) !== false)
        {
            if (query("UPDATE users SET cash = cash + $cash WHERE id = $id") !== false)
            {
                query("INSERT INTO history(id, symbol,shares,price, action) VALUES($id, '$symbol',$amount,$price, 'SELL')");
                success("Transaction successful!");
            }
        }
        else
        {
            apologize("Database error");
        }

        getPositions();
    }
