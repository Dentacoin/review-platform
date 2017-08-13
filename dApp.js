/*
Reviews dApp for dentist and patient wallet

*/



window.addEventListener('load', function() {

  // Checking if Web3 has been injected by the browser (Mist/MetaMask)
  if (typeof web3 !== 'undefined') {
    // Use Mist/MetaMask's provider
    window.web3 = new Web3(web3.currentProvider);
  } else {
    console.log('No web3? You should consider trying MetaMask!')
    // fallback - use your fallback strategy (local node / hosted node + in-dapp id mgmt / fail)
    window.web3 = new Web3(new Web3.providers.HttpProvider("http://localhost:8545"));
  }

  const contractAdr = ""; //Rinkeby Testnet


  const abi = [ { "constant": true, "inputs": [], "name": "lockTime", "outputs": [ { "name": "", "type": "uint256", "value": "600" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [], "name": "withdraw", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "startTime", "outputs": [ { "name": "", "type": "uint256", "value": "1502497439" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "owner", "outputs": [ { "name": "", "type": "address", "value": "0x8196cd5fe0eec770de925be7a6d0fc79d06ef891" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "tokenAddress", "outputs": [ { "name": "", "type": "address", "value": "0x2debb13bcf5526e0cf5e3a4e5049100e3f7c2ae5" } ], "payable": false, "type": "function" }, { "inputs": [], "payable": false, "type": "constructor" }, { "payable": true, "type": "fallback" } ];



    // hide all messages
    $("#newTokenResponse").hide();
    $("#transferTokenResponse").hide();
    //$("#checkBalanceResponse").hide();




    var account = web3.eth.accounts[0];





    var accountInterval = setInterval(function() {

      //auto refresh account
      if (web3.eth.accounts[0] !== account) {
        account = web3.eth.accounts[0];
      }
      //auto refresh balance
      token.balanceOf(account, function(error, balance){
          return $("#checkBalanceResponse_body").html(String(balance.toString(10)) + " ٨");
      });
      // auto refresh address
      $("#myAddress").html(account);
    }, 1000);






    //- Check balance
        $("#_checkBalance").click(function(){
            //var account = selectedAccount;

            token.balanceOf(account, function(error, balance){
                //$("#checkBalanceResponse").show();
                return $("#checkBalanceResponse_body").html(String(balance.toString(10)) + " ٨");
            });
        });
    //- Check balance




    // Transfer Dentacoins
        $("#_transfer").click(function(){
            var account = $("#_transferAccount").val(),
                    amount = parseInt($("#_transferAmount").val());

            console.log("Transfer Details", account, amount);

            // transfer tokens
            token.transfer(account, amount, transactionObject, function(error, transactionHash){
                if(error) {
                    $("#transferTokenResponse").show();
                    return $("#transferTokenResponse_body").html("There was an error transfering your Dentacoins: " + String(error));
                }

                $("#transferTokenResponse").show();
                //return $("#transferTokenResponse_body").html("Your token is being transfered with tx hash: " + String(transactionHash));
                return $("#transferTokenResponse_body").html("Ok, pending transaction. Give it a minute and check for confirmation on <a href='https://etherscan.io/tx/" + String(transactionHash) + "' target='_blank'>Etherscan</a> ");
            });

            token.Transfer({}, function(error, result){
                if(error) {
                    $("#transferTokenResponse").show();
                    return $("#transferTokenResponse_body").html("There was an error transfering your Dentacoins: " + String(error));
                }

                $("#transferTokenResponse").show();
                return $("#transferTokenResponse_body").html("Your Dentacoins have been transfered! " + String(result.transactionHash));
            });
        });
    //- Transfer Dentacoins




})
