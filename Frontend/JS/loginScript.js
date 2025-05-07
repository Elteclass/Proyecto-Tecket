const container = document.querySelector(".container");
const btnReport = document.getElementById("btn-report");
const btnSignUp = document.getElementById("btn-sign-up");

btnReport.addEventListener("click", ()=>{
    container.classList.remove("toggle");
})
btnSignUp.addEventListener("click", ()=>{
    container.classList.add("toggle");
})