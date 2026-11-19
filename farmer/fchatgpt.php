<?php
include ('fsession.php');
ini_set('memory_limit', '-1');

if(!isset($_SESSION['farmer_login_user'])){
header("location: ../index.php");} // Redirecting To Home Page
$query4 = "SELECT * from farmerlogin where email='$user_check'";
              $ses_sq4 = mysqli_query($conn, $query4);
              $row4 = mysqli_fetch_assoc($ses_sq4);
              $para1 = $row4['farmer_id'];
              $para2 = $row4['farmer_name'];
			  
?>

<!DOCTYPE html>
<html>
<?php require ('fheader.php');  ?>
 <style>

.chat-box {
  height: 59vh; 
  overflow-y: scroll;
}


		.message {
			margin-bottom: 10px;
			padding: 10px;
			padding-bottom: 0;
			border-radius: 10px;
			display: inline-block;
			max-width: 85%;
		
			
			word-wrap: break-word;
			white-space: normal;
		}

		.left-side {
			background-color: lightgray;
			float: left;
		}

		.right-side {
			background-color: lightgreen;
			float: right;
		}		
			
		.popup {
			position: fixed;
			bottom: 20vh;
			left: 50%;
			transform: translateX(-50%);
			background-color: rgba(0, 0, 0, 0.6);
			color: white;
			border-radius: 5px;
			padding: 10px 20px;
			font-size: 16px;
			display: none;
		}
				
 </style>
  <body class="bg-white" id="top">
  
<?php include ('fnav.php');  ?>
 	
  <section class="section section-shaped section-lg">
    <div class="shape shape-style-1 shape-primary">
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
      <span></span>
    </div>
<!-- ======================================================================================================================================== -->


    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 ">
			
				<div class="card text-white bg-gradient-white mt--6">
				
				<div class="card-header bg-gradient-secondary d-flex">

					<span class="text-default display-4">
						<img src="../assets/img/chatgpt.svg" class="rounded-circle img-fluid" width="" alt="Chat GPT Logo"> Chat GPT
					</span>

					<div class="ml-auto">
						<button class="btn btn-info" onclick="window.print()">Print</button>
						<button class="btn btn-danger mr-2 " onclick="clearContent()">Clear Chat</button>					
					</div>								
				</div>

				 <div class="card-body chat-box rounded p1" id="chatbox"><span id="copy-popup" class="popup">Copied!</span></div>
				 							
							 <div class="card-footer bg-gradient-secondary">
                                <div class="form-group row">
                                    <div class="col-md-10 mb-1">
                                        <input id="userInput" type="text" class="form-control text-dark"
                                            placeholder="Type your message here..." />
                                    </div>

                                    <div class="col-md-2">
                                        <input id="sendButton" type="button" value="SUBMIT" class=" form-control btn btn-success btn-block" />
                                    </div>
                                </div>
                            </div>
							
	 </div>

    </div>
       </div>
	</div>
		
</section>

    <?php require("footer.php");?>
<script>
const chatbox = document.getElementById("chatbox");
const userInput = document.getElementById("userInput");
const sendButton = document.getElementById("sendButton");
let messages = [];
let activeRequest = null;
let requestTimeout = null;
let requestVersion = 0;

function clearContent() {
    requestVersion++;
    if (activeRequest) {
        activeRequest.abort();
        activeRequest = null;
    }
    window.clearTimeout(requestTimeout);
    requestTimeout = null;
    messages = [];
    chatbox.replaceChildren();
    const copyPopup = document.createElement("span");
    copyPopup.id = "copy-popup";
    copyPopup.className = "popup";
    copyPopup.textContent = "Copied!";
    chatbox.appendChild(copyPopup);
    sendButton.value = "SUBMIT";
    sendButton.disabled = false;
}

function renderMessage(element, content) {
    if (typeof window.markdownit === "function") {
        element.innerHTML = window.markdownit({ html: false }).render(content);
    } else {
        element.textContent = content;
        element.style.whiteSpace = "pre-wrap";
    }
}

function appendErrorMessage(message) {
    const errorMessage = document.createElement("div");
    errorMessage.className = "message left-side text-danger";
    errorMessage.textContent = message;
    const wrapper = document.createElement("pre");
    wrapper.appendChild(errorMessage);
    chatbox.appendChild(wrapper);
    chatbox.scrollTop = chatbox.scrollHeight;
}

function showCopyStatus(message) {
    const copyPopup = document.getElementById("copy-popup");
    if (!copyPopup) {
        return;
    }
    copyPopup.textContent = message;
    copyPopup.style.display = "block";
    window.setTimeout(function() {
        copyPopup.style.display = "none";
    }, 1000);
}

function copyResponse(content) {
    if (navigator.clipboard && typeof navigator.clipboard.writeText === "function" && window.isSecureContext) {
        return navigator.clipboard.writeText(content);
    }

    return new Promise(function(resolve, reject) {
        const textarea = document.createElement("textarea");
        textarea.value = content;
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        document.body.appendChild(textarea);
        try {
            textarea.select();
            if (document.execCommand("copy")) {
                resolve();
            } else {
                reject(new Error("Copy failed."));
            }
        } finally {
            document.body.removeChild(textarea);
        }
    });
}

function appendAssistantMessage(content) {
    const messageElement = document.createElement("div");
    messageElement.className = "message left-side";
    renderMessage(messageElement, content);

    function copy() {
        copyResponse(content).then(function() {
            showCopyStatus("Copied!");
        }).catch(function() {
            showCopyStatus("Copy failed");
        });
    }

    const copyIcon = document.createElement("i");
    copyIcon.className = "far fa-clipboard ml-1 btn btn-outline-dark";
    copyIcon.setAttribute("role", "button");
    copyIcon.setAttribute("tabindex", "0");
    copyIcon.setAttribute("aria-label", "Copy response");
    copyIcon.addEventListener("click", copy);
    copyIcon.addEventListener("keydown", function(event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            copy();
        }
    });

    const wrapper = document.createElement("pre");
    wrapper.append(messageElement, copyIcon);
    chatbox.appendChild(wrapper);
    chatbox.scrollTop = chatbox.scrollHeight;
}

function getRequestMessages(pendingMessage) {
    const allMessages = messages.concat([pendingMessage]);
    const requestMessages = [];
    let totalLength = 0;

    for (let index = allMessages.length - 1; index >= 0 && requestMessages.length < 40; index--) {
        const message = allMessages[index];
        const messageLength = new Blob([message.content]).size;
        if (totalLength + messageLength > 24000) {
            break;
        }
        requestMessages.unshift(message);
        totalLength += messageLength;
    }

    if (requestMessages.length && requestMessages[0].role === "assistant") {
        requestMessages.shift();
    }
    return requestMessages;
}

sendButton.addEventListener("click", function() {
    const message = userInput.value.trim();
    if (!message || activeRequest) {
        return;
    }

    if (new Blob([message]).size > 12000) {
        appendErrorMessage("That message is too long. Please keep it under 12 KB.");
        return;
    }

    const userMessage = { role: "user", content: message };
    const userMessageElement = document.createElement("div");
    userMessageElement.className = "message right-side";
    renderMessage(userMessageElement, message);
    const wrapper = document.createElement("pre");
    wrapper.appendChild(userMessageElement);
    chatbox.appendChild(wrapper);
    chatbox.scrollTop = chatbox.scrollHeight;
    userInput.value = "";
    sendButton.value = "Generating Response...";
    sendButton.disabled = true;
    fetchMessages(userMessage);
});

userInput.addEventListener("keydown", function(event) {
    if (event.key === "Enter" && !event.isComposing) {
        event.preventDefault();
        if (!sendButton.disabled) {
            sendButton.click();
        }
    }
});

function fetchMessages(userMessage) {
    const thisRequest = requestVersion;
    const controller = new AbortController();
    activeRequest = controller;
    const loadingMessage = document.createElement("div");
    loadingMessage.className = "message left-side text-muted";
    loadingMessage.textContent = "Thinking...";
    const loadingWrapper = document.createElement("pre");
    loadingWrapper.appendChild(loadingMessage);
    chatbox.appendChild(loadingWrapper);
    chatbox.scrollTop = chatbox.scrollHeight;

    requestTimeout = window.setTimeout(function() {
        controller.abort();
    }, 70000);

    fetch("../openai.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        credentials: "same-origin",
        cache: "no-store",
        signal: controller.signal,
        body: JSON.stringify({
            mode: "chat",
            messages: getRequestMessages(userMessage)
        })
    }).then(function(response) {
        return response.json().catch(function() {
            throw new Error("The server returned an invalid response.");
        }).then(function(data) {
            if (!response.ok) {
                const errorMessage = data && data.error && typeof data.error.message === "string"
                    ? data.error.message
                    : "The assistant is unavailable. Check the server configuration and try again.";
                throw new Error(errorMessage);
            }
            return data;
        });
    }).then(function(response) {
        if (thisRequest !== requestVersion) {
            return;
        }

        const reply = response && response.choices && response.choices[0]
            ? response.choices[0].message
            : null;
        if (!reply || reply.role !== "assistant" || typeof reply.content !== "string" || !reply.content.trim()) {
            appendErrorMessage("The assistant returned an invalid response. Please try again.");
            if (!userInput.value.trim()) {
                userInput.value = userMessage.content;
            }
            return;
        }

        messages.push(userMessage, { role: "assistant", content: reply.content });
        if (messages.length > 40) {
            messages = messages.slice(-40);
        }
        appendAssistantMessage(reply.content);
    }).catch(function(error) {
        if (thisRequest !== requestVersion || error.name === "AbortError") {
            if (thisRequest === requestVersion) {
                appendErrorMessage("The request timed out. Please try again.");
            }
            return;
        }
        appendErrorMessage(error instanceof TypeError
            ? "Could not connect to the server. Check your connection and try again."
            : error.message);
        if (!userInput.value.trim()) {
            userInput.value = userMessage.content;
        }
    }).finally(function() {
        if (thisRequest === requestVersion) {
            window.clearTimeout(requestTimeout);
            requestTimeout = null;
            loadingWrapper.remove();
            activeRequest = null;
            sendButton.value = "SUBMIT";
            sendButton.disabled = false;
        }
    });
}
 </script>
  
</body>
</html>