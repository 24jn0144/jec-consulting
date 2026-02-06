<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>期末試験対策クイズ02</title>
  <style>
    body { font-family: sans-serif; max-width: 900px; margin: 20px auto; line-height: 1.6; }
    h1 { font-size: 1.6rem; }
    .card { border: 1px solid #ccc; border-radius: 8px; padding: 16px; margin-top: 16px; }
    .choice { margin: 8px 0; }
    button { padding: 8px 16px; margin-top: 12px; cursor: pointer; }
    .result { margin-top: 12px; font-weight: bold; }
  </style>
</head>
<body>

<h1>期末試験対策クイズ02（全問ランダム）</h1>

<div class="card">
  <div id="q-number"></div>
  <div id="question" style="font-weight:bold; margin-bottom:12px;"></div>

  <div id="choices"></div>

  <button id="checkBtn">答え合わせ</button>
  <button id="nextBtn">次の問題</button>

  <div id="result" class="result"></div>
  <div id="correctAnswer" class="result"></div>
</div>

<script>
  const quizData = [
    {
      q: "[新規データセンターを建設した。これは運用的支出に該当する。これは正しい内容でしょうか？]",
      choices: ["正しい", "正しくない"],
      a: ["正しくない"]
    },
    {
      q: "[Azure仮想マシンから、社内のSQLServerデータベースへクエリーを実行するクラウド環境は、”ハイブリッドクラウド”である。太字部分(””)を修正してください]",
      choices: ["変更不要", "パブリッククラウド", "プライベートクラウド", "オンプレミス"],
      a: ["変更不要"]
    },
    {
      q: "[あなたは、 社内のアプリケーションを Azure へ移行する予定です。 なお、 移行予定のアプリケーションを実行するには、 前提条件として、 別のアプ リケーションやサービスが必要です。 移行後は運用管理のコストをできる 限り抑えるつもりです。 アプリケーションの最適な移行先を1つ選択して ください。]",
      choices: ["SaaS", "PaaS", "IaaS", "オンプレミス", "SaaSまたはPaaS"],
      a: ["IaaS"]
    },
    {
      q: "[次のうち仮想マシンの作成時の課金変動要素となるものを全て選んでください]",
      choices: ["仮想マシンのOS対応", "仮想マシンを作成するリージョン", "仮想マシンの利用ユーザ数", "仮想マシンのサイズ"],
      a: ["仮想マシンのOS対応", "仮想マシンを作成するリージョン", "仮想マシンのサイズ"]
    },
    {
      q: "[以下のサービスのうちAzureでサーバレスコンピューティングを提供するサービスはどれですか？]",
      choices: ["Azure VirtualMachines", "Azure Functions", "Azure Virtual Desktop", "Azure VirtualMachine ScaleSet"],
      a: ["Azure Functions"]
    },
    {
      q: "[あなたの会社では、アプリケーションの設定ファイルを格納するクラウド ストレージとして、 Azure Storage を採用する予定です。 適切な Azure Storage のデータサービスを2つ選択してください。]",
      choices: ["BLOB", "ファイル共有", "テーブル", "キュー"],
      a: ["BLOB", "ファイル共有"]
    },
    {
      q: "[Azure上に仮想マシンそれぞれWEBサーバ、アプリケーションサーバ、DBサーバを構築しました。WEBサーバとDBサーバの通信制御のために最も簡単な方法はどれですか？]",
      choices: ["Azure VirtualNetwork", "ネットワークセキュリティグループ", "Azure Container Instances", "AzureDedicatedHost"],
      a: ["ネットワークセキュリティグループ"]
    },
    {
      q: "[仮想マシンにリモートデスクトップ接続をする必要があります。ポート番号3389の通信許可をする場合にどの設定を確認する必要がありますか？]",
      choices: ["AzureExpressRoute", "ネットワークセキュリティグループ", "Azure VirtualNetwork", "AzureVPN接続"],
      a: ["ネットワークセキュリティグループ"]
    },
    {
      q: "[〇〇は、ApacheSparkベースの分析サービスです。〇〇に入る正しい用語を用語を選択して下さい]",
      choices: ["Azure Data Bricks", "Azure Data Factory", "Azure DevOps", "Azure HD Insight"],
      a: ["Azure Data Bricks"]
    },
    {
      q: "[あなたの会社はオンプレミス環境にある共通言語ランタイム（CLR）を利用するSQLサーバーデータベースをAzureに移行する予定です。移行時と運用時のコストも最小限にするための方法を選択してください。]",
      choices: ["Azure SQL Managed Instance", "Azure SQL Databaseへと移行", "仮想マシンにSQLサーバーデータベースを移行する", "Azure FunctionsにSQLサーバーデータベースを移行する。"],
      a: ["Azure SQL Managed Instance"]
    },
    {
      q: "[Hadoopが利用可能なビッグデータ分散処理、分析サービスはなんでしょうか？]",
      choices: ["Azure HD insight", "Azure Data Lake Analytics", "Azure Synapse Analytics", "Azure SQL Database"],
      a: ["Azure HD insight"]
    },
    {
      q: "[ノーコード/ローコード開発で迅速にアプリケーションを開発できるサービスは次 のうちどれですか?]",
      choices: ["Azure loT Hub", "Azure Bot Service", "Azure Functions", "Power Apps"],
      a: ["Power Apps"]
    },
    {
      q: "[〇〇〇〇はコードのデプロイメントのためのソリューションです。〇〇〇〇に入 る正しい用語を選択してください。]",
      choices: ["Azure Advisor", "Azure Cognitive Services", "Azure Application Insights", "Azure DevOps"],
      a: ["Azure DevOps"]
    },
    {
      q: "[Azure Monitor に関する下記の記述で正しいものをすべて選択してください。]",
      choices: ["オンプレミスのサーバーを監視する", "Azure EntraIDの特定のグループにアラートを通知する", "Azure Log Analyticsのワークスペース上のデータをトリガーにアラートを通知する"],
      a: ["オンプレミスのサーバーを監視する", "Azure EntraIDの特定のグループにアラートを通知する", "Azure Log Analyticsのワークスペース上のデータをトリガーにアラートを通知する"]
    },
    {
      q: "[Just-In-Time VM アクセスを有効にするサービスはどれですか?]",
      choices: ["Microsoft Defender for Cloud", "Azure Entra ID", "RBAC", "Microsoft Sentinel"],
      a: ["Microsoft Defender for Cloud"]
    },
    {
      q: "[オンプレミスのID を Microsoft Entra  と同期させる機能はどれですか?]",
      choices: ["Microsoft Entra Connect Identity Protection", "Microsoft Entra ID", "Microsoft Entra Connect", "Microsoft Entra ConnectDomainService"],
      a: ["Microsoft Entra Connect"]
    },
    {
      q: "[Azure のサービスがパブリックプレビュープログラムから一般公開 (GA) に変更に なりました。 パブリックプレビューと比較した際のGAの違いとして最も当てはまら ないものを1つ選択してください。]",
      choices: ["今後はSLAの保証対象となる", "通常のAzure Portal からで も該当サービスのリソースが作 成可能になる", "該当サービスが後継サービスなしでサービス廃止される場合、 12ヶ月前までには通知されるようになる", "正式なカスタマーサポートの対象となる"],
      a: ["通常のAzure Portal からで も該当サービスのリソースが作 成可能になる"]
    },
    {
      q: "[Webサーバーに Azure Virtual Machines を使用します。 Azure Virtual Machines のSLAで保証されている稼働率は99.9%です。 このAzure Virtual Machines に可用 性セットオプションを使用した場合、 稼働率はどのように変化しますか?]",
      choices: ["99.9% よりも上がる", "99.9% のまま変わらない", "99.9% よりも下がる"],
      a: ["99.9% よりも上がる"]
    }
  ];

  const order = [...Array(quizData.length).keys()];
  for (let i = order.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [order[i], order[j]] = [order[j], order[i]];
  }

  let current = 0;

  function loadQuestion() {
    if (current >= order.length) {
      document.querySelector(".card").innerHTML =
        "<h2>全問終了！お疲れさま！</h2><p>もう一度やる場合はページをリロードしてください。</p>";
      return;
    }

    const item = quizData[order[current]];

    document.getElementById("q-number").textContent =
      `問題 ${current + 1} / 全 ${quizData.length} 問`;
    document.getElementById("question").textContent = item.q;

    const choiceArea = document.getElementById("choices");
    choiceArea.innerHTML = "";

    const isMulti = item.a.length > 1;

    item.choices.forEach(c => {
      const div = document.createElement("div");
      div.className = "choice";
      div.innerHTML = `
        <label>
          <input type="${isMulti ? "checkbox" : "radio"}" name="choice" value="${c}">
          ${c}
        </label>`;
      choiceArea.appendChild(div);
    });

    document.getElementById("result").textContent = "";
    document.getElementById("correctAnswer").textContent = "";
  }

  function checkAnswer() {
    const item = quizData[order[current]];
    const correct = item.a;

    const selected = [...document.querySelectorAll("input[name='choice']:checked")]
      .map(i => i.value);

    if (selected.length === 0) {
      document.getElementById("result").textContent = "選択肢を選んでください。";
      return;
    }

    const isCorrect =
      selected.length === correct.length &&
      selected.every(v => correct.includes(v));

    document.getElementById("result").textContent =
      isCorrect ? "◎ 正解！" : "× 不正解";

    document.getElementById("correctAnswer").textContent =
      "正解： " + correct.join("、");
  }

  document.getElementById("checkBtn").addEventListener("click", checkAnswer);
  document.getElementById("nextBtn").addEventListener("click", () => {
    current++;
    loadQuestion();
  });

  loadQuestion();
</script>

</body>
</html>