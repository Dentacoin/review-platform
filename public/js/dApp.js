/*
Reviews dApp for dentist / patient wallet / submit / admin

*/


// web3 loader Metamask/Mist ---------------------------------------------------

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


  // setup transaction object
  var transactionObject = {
      from: web3.eth.accounts[0],
      gas: 100000
  };

// Dentacoin token integration -------------------------------------------------

  // setup DCN token address
  const DCNaddress = "0x2Debb13BCF5526e0cF5E3A4E5049100E3F7c2AE5";  // Rinkeby TESTNET

  // setup token contract ABI
  const tokenABI =[ { "constant": true, "inputs": [], "name": "sellPriceEth", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [], "name": "buyDentacoinsAgainstEther", "outputs": [ { "name": "amount", "type": "uint256" } ], "payable": true, "type": "function" }, { "constant": true, "inputs": [], "name": "name", "outputs": [ { "name": "", "type": "string", "value": "Dentacoin" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_spender", "type": "address" }, { "name": "_value", "type": "uint256" } ], "name": "approve", "outputs": [ { "name": "success", "type": "bool" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newGasReserveInWei", "type": "uint256" } ], "name": "setGasReserve", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "totalSupply", "outputs": [ { "name": "", "type": "uint256", "value": "8000000000000" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_from", "type": "address" }, { "name": "_to", "type": "address" }, { "name": "_value", "type": "uint256" } ], "name": "transferFrom", "outputs": [ { "name": "success", "type": "bool" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "decimals", "outputs": [ { "name": "", "type": "uint8", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newDCNAmount", "type": "uint256" } ], "name": "setDCNForGas", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "directTradeAllowed", "outputs": [ { "name": "", "type": "bool", "value": true } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "minBalanceForAccounts", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newBuyPriceEth", "type": "uint256" }, { "name": "newSellPriceEth", "type": "uint256" } ], "name": "setEtherPrices", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "buyPriceEth", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "amountOfEth", "type": "uint256" }, { "name": "dcn", "type": "uint256" } ], "name": "refundToOwner", "outputs": [], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newGasAmountInWei", "type": "uint256" } ], "name": "setGasForDCN", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "_owner", "type": "address" } ], "name": "balanceOf", "outputs": [ { "name": "balance", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "amount", "type": "uint256" } ], "name": "sellDentacoinsAgainstEther", "outputs": [ { "name": "revenue", "type": "uint256" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [], "name": "haltDirectTrade", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "owner", "outputs": [ { "name": "", "type": "address", "value": "0x8196cd5fe0eec770de925be7a6d0fc79d06ef891" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "symbol", "outputs": [ { "name": "", "type": "string", "value": "٨" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_to", "type": "address" }, { "name": "_value", "type": "uint256" } ], "name": "transfer", "outputs": [ { "name": "success", "type": "bool" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "DentacoinAddress", "outputs": [ { "name": "", "type": "address", "value": "0x2debb13bcf5526e0cf5e3a4e5049100e3f7c2ae5" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "DCNForGas", "outputs": [ { "name": "", "type": "uint256", "value": "10" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "gasForDCN", "outputs": [ { "name": "", "type": "uint256", "value": "5000000000000000" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "minimumBalanceInWei", "type": "uint256" } ], "name": "setMinBalance", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "_owner", "type": "address" }, { "name": "_spender", "type": "address" } ], "name": "allowance", "outputs": [ { "name": "remaining", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [], "name": "unhaltDirectTrade", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "gasReserve", "outputs": [ { "name": "", "type": "uint256", "value": "1000000000000000000" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newOwner", "type": "address" } ], "name": "transferOwnership", "outputs": [], "payable": false, "type": "function" }, { "inputs": [], "payable": false, "type": "constructor" }, { "payable": true, "type": "fallback" }, { "anonymous": false, "inputs": [ { "indexed": true, "name": "_from", "type": "address" }, { "indexed": true, "name": "_to", "type": "address" }, { "indexed": false, "name": "_value", "type": "uint256" } ], "name": "Transfer", "type": "event" }, { "anonymous": false, "inputs": [ { "indexed": true, "name": "_owner", "type": "address" }, { "indexed": true, "name": "_spender", "type": "address" }, { "indexed": false, "name": "_value", "type": "uint256" } ], "name": "Approval", "type": "event" } ];


  // setup Token contract factory
  const Token = web3.eth.contract(tokenABI);


  // setup token instance
  const token = Token.at(DCNaddress);





// review.sol integration ------------------------------------------------------

  const contractAdr = "0x1841381F27789fb10662f633025b875C0c77Ffcb"; //Rinkeby Testnet


  const abi = [ { "constant": true, "inputs": [], "name": "count", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "", "type": "bytes32" } ], "name": "hashedInviteSecret", "outputs": [ { "name": "", "type": "bool", "value": false } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "hashedInSecret", "outputs": [ { "name": "", "type": "bytes32", "value": "0x0000000000000000000000000000000000000000000000000000000000000000" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_dentist", "type": "address" } ], "name": "setDentistOnWhitelist", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "dcnAmount", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "", "type": "uint256" } ], "name": "hashedSubmitSecrets", "outputs": [ { "name": "", "type": "bytes32", "value": "0x" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "dcnAmountTrusted", "outputs": [ { "name": "", "type": "uint256", "value": "0" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "owner", "outputs": [ { "name": "", "type": "address", "value": "0x8196cd5fe0eec770de925be7a6d0fc79d06ef891" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "", "type": "address" } ], "name": "dentistWhitelist", "outputs": [ { "name": "", "type": "bool", "value": false } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_arrayOfHashedSecrets", "type": "bytes32[]" } ], "name": "addSubmitSecrets", "outputs": [], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_secret", "type": "bytes32" } ], "name": "setInviteSecret", "outputs": [], "payable": false, "type": "function" }, { "constant": true, "inputs": [], "name": "tokenAddress", "outputs": [ { "name": "", "type": "address", "value": "0x2debb13bcf5526e0cf5e3a4e5049100e3f7c2ae5" } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_to", "type": "address" }, { "name": "_toID", "type": "uint256" }, { "name": "_content", "type": "bytes32" }, { "name": "_submitSecret", "type": "bytes16" }, { "name": "_inviteSecret", "type": "bytes32" } ], "name": "submitReview", "outputs": [ { "name": "success", "type": "bool" } ], "payable": false, "type": "function" }, { "constant": true, "inputs": [ { "name": "", "type": "uint256" } ], "name": "reviewID", "outputs": [ { "name": "timeStamp", "type": "uint256", "value": "0" }, { "name": "writtenByAddress", "type": "address", "value": "0x0000000000000000000000000000000000000000" }, { "name": "writtenForAddress", "type": "address", "value": "0x0000000000000000000000000000000000000000" }, { "name": "writtenForID", "type": "uint256", "value": "0" }, { "name": "content", "type": "bytes32", "value": "0x0000000000000000000000000000000000000000000000000000000000000000" }, { "name": "trusted", "type": "bool", "value": false } ], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "_dcnAmount", "type": "uint256" }, { "name": "_dcnAmountTrusted", "type": "uint256" } ], "name": "setDCNAmounts", "outputs": [], "payable": false, "type": "function" }, { "constant": false, "inputs": [ { "name": "newOwner", "type": "address" } ], "name": "transferOwnership", "outputs": [], "payable": false, "type": "function" }, { "inputs": [], "payable": false, "type": "constructor" }, { "payable": true, "type": "fallback" } ];

  // setup review contract
  const Contract = web3.eth.contract(abi);


  // setup contract instance
  const contract = Contract.at(contractAdr);



// Wallet for dentists and patients --------------------------------------------

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











// Patients - submit reviews ---------------------------------------------------

    // Submit review
        $("#_submit").click(function(){
            var dAddress = $("#_dentistAddress").val();                         // eth address
            var dId = $("#_dentistId").val();                                   // integer
            var content = $("#_reviewContent").val();                           // 32 digit bytes (hash)
            var submitSecret = $("#_submitSecret").val();                       // 16 digit bytes
            var inviteSecret = $("#_inviteSecret").val();                       //

            console.log("Submit Details", dAddress, dId, content, submitSecret, inviteSecret);

            // submit function
            contract.submitReview(dAddress, dId, content, submitSecret, inviteSecret, transactionObject, function(error, confirmed){
                if(error) {
                    return $("#submitResult").html("There was an error transfering your Review: " + String(error));
                }

                return $("#submitResult").html("Your review is confirmed: " + String(confirmed));
            });
/*
            contract.SubmitEvent({}, function(error, result){
                if(error) {
                    //return $("#transferTokenResponse_body").html("There was an error transfering your Dentacoins: " + String(error));
                }

                $("#transferTokenResponse").show();
                //return $("#transferTokenResponse_body").html("Your Dentacoins have been transfered! " + String(result.transactionHash));
            });
*/
        });
    //- Transfer Dentacoins







// Dentists - invite patients --------------------------------------------------

    // Set inviteSecret
        $("#_invite").click(function(){
            var inviteSec = $("#_inviteSec").val();                         // eth address

            console.log("Invite Details", inviteSec);

            // submit function
            contract.setInviteSecret(inviteSec, transactionObject, function(error, confirmed){
                if(error) {
                    return $("#inviteResult").html("There was an error sending the invite: " + String(error));
                }

                return $("#inviteResult").html("Your invite is confirmed: " + String(confirmed));
            });
    /*
            contract.SubmitEvent({}, function(error, result){
                if(error) {
                    //return $("#transferTokenResponse_body").html("There was an error transfering your Dentacoins: " + String(error));
                }

                $("#transferTokenResponse").show();
                //return $("#transferTokenResponse_body").html("Your Dentacoins have been transfered! " + String(result.transactionHash));
            });
    */
        });

});
